# general todo:
# move the tfstate in secure place and then remove it from .gitignore ?
# what is the difference between "Consumption" and "Workload profiles" for Azure Container Apps ?
locals {
  gally_project_name           = "${lower(var.gally_prefix)}${title(var.project_name)}"
  resource_group_name          = "${local.gally_project_name}-rg"
  vnet_name                    = "${local.gally_project_name}-vnet"
  log_analytics_workspace_name = "${local.gally_project_name}-law"
  container_app_env_name       = "${local.gally_project_name}-cae"
  container_registry_name      = "${local.gally_project_name}Registry"
  #todo add /gally/ on image name between the login url and image name
  image_name = "${azurerm_container_registry.cr.login_server}/gally/${var.gally_prefix}-%s:${var.gally_version}"
  docker_images_to_push = {
    router = {
      label = "router"
      context    = "../docker/router"
    }
#     varnish = {
#       context    = "../docker/varnish"
#       build_args = {}
#     }
#     search = {
#       context    = "../docker/search/"
#       build_args = {}
#     }
#     search-ml = {
#       context    = "../docker/search/"
#       build_args = {}
#     }
#     php = {
#       context = "../api"
#       build_args = {
#         COMPOSER_AUTH = var.composer_auth
#       }
#     }
#     pwa = {
#       context = "../front"
#       build_args = {
#         NEXT_PUBLIC_ENTRYPOINT = "#todo"
#         NEXT_PUBLIC_API_URL    = "#todo"
#       }
#     }
  }
}

# Resource Group
resource "azurerm_resource_group" "rg" {
  name     = local.resource_group_name
  location = var.location
}

# Container registry
resource "azurerm_container_registry" "cr" {
  #todo limiter l'utilisation du regisstre à des utilisateurs, car tout le monde peut pousser et récupérer des images
  # voir dans la doc comment encrypter la connexion
  location            = azurerm_resource_group.rg.location
  name                = local.container_registry_name
  resource_group_name = azurerm_resource_group.rg.name
  sku                 = "Basic" #todo check is Standard is better in our case ?
  admin_enabled       = true
}

# build images
resource "docker_image" "build_images" {
  for_each     = local.docker_images_to_push
  name         = format(local.image_name, each.key)
  keep_locally = false

  build {
    no_cache   = true
    context    = "${path.cwd}/${each.value.context}"
    build_args = try(each.value.build_args, {})
  }

  triggers = {
    #todo check what it does and if it works
    dir_sha1 = sha1(join("", [for f in fileset(path.cwd, "${each.value.context}/*") : filesha1(f)]))
  }
}

resource "docker_registry_image" "push_image_to_cr" {
  for_each      = local.docker_images_to_push
  name          = docker_image.build_images[each.key].name
  keep_remotely = false

  triggers = {
    #todo check what it does and if it works
    dir_sha1 = sha1(join("", [for f in fileset(path.cwd, "${each.value.context}/*") : filesha1(f)]))
  }
}

resource "azurerm_log_analytics_workspace" "law" {
    location            = azurerm_resource_group.rg.location
    name                = local.log_analytics_workspace_name
    resource_group_name = azurerm_resource_group.rg.name
}

# Virtual network todo: remove if not used
# resource "azurerm_virtual_network" "vnet" {
#   name                = local.vnet_name
#   address_space       = ["10.0.0.0/16"]
#   location            = azurerm_resource_group.rg.location
#   resource_group_name = azurerm_resource_group.rg.name
# }

resource "azurerm_container_app_environment" "acae" {
    location                   = azurerm_resource_group.rg.location
    name                       = local.container_app_env_name
    resource_group_name        = azurerm_resource_group.rg.name
    log_analytics_workspace_id = azurerm_log_analytics_workspace.law.id
}

resource "azurerm_container_app" "router" {
    container_app_environment_id = azurerm_container_app_environment.acae.id
    name                         = lower("${local.gally_project_name}-${local.docker_images_to_push.router.label}-aca")
    resource_group_name          = azurerm_resource_group.rg.name
    revision_mode                = "Single"

    secret {
        name  = "registry-credentials"
        value = azurerm_container_registry.cr.admin_password
    }

    registry {
        server = azurerm_container_registry.cr.login_server
        username = azurerm_container_registry.cr.admin_username
        password_secret_name = "registry-credentials"
    }

    template {
        container {
            name   = "router"
            image  = format(local.image_name, local.docker_images_to_push.router.label)
            cpu    = "0.5"
            memory = "1Gi"
        }
        max_replicas = 1
        min_replicas = 1
    }
}


