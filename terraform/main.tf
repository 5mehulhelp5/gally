provider "azurerm" {
  features {}
}

locals {
  gally_project_name           = "${lower(var.gally_prefix)}${title(var.project_name)}"
  resource_group_name          = "${local.gally_project_name}-rg"
  vnet_name                    = "${local.gally_project_name}-vnet"
  log_analytics_workspace_name = "${local.gally_project_name}-law"
  container_app_env_name       = "${local.gally_project_name}-cae"
  container_registry_name      = "${local.gally_project_name}Registry"
}

# Resource Group
resource "azurerm_resource_group" "rg" {
  name     = local.resource_group_name
  location = var.location
}


# Virtual network todo: remove if not used
# resource "azurerm_virtual_network" "vnet" {
#   name                = local.vnet_name
#   address_space       = ["10.0.0.0/16"]
#   location            = azurerm_resource_group.rg.location
#   resource_group_name = azurerm_resource_group.rg.name
# }

resource "azurerm_log_analytics_workspace" "law" {
  location            = azurerm_resource_group.rg.location
  name                = local.log_analytics_workspace_name
  resource_group_name = azurerm_resource_group.rg.name
}

resource "azurerm_container_app_environment" "acae" {
  location                   = azurerm_resource_group.rg.location
  name                       = local.container_app_env_name
  resource_group_name        = azurerm_resource_group.rg.name
  log_analytics_workspace_id = azurerm_log_analytics_workspace.law.id
}


# Container registry
resource "azurerm_container_registry" "cr" {
  #todo limiter l'utilisation du regisstre à des utilisateurs, car tout le monde peut pousser et récupérer des images
  # voir dans la doc comment encrypter la connexion
  location            = azurerm_resource_group.rg.location
  name                = local.container_registry_name
  resource_group_name = azurerm_resource_group.rg.name
  sku                 = "Basic" #todo check is Standard is better in our case ?
}


resource "azurerm_container_app" "web" {
    container_app_environment_id = azurerm_container_app_environment.acae.id
    name                         = "web"
    resource_group_name          = azurerm_resource_group.rg.name
    revision_mode                = "Simple"

    template {
        container {
            name   = "nginx"
            image  = "${azurerm_container_registry.cr.login_server}/gally/gally-nginx:${var.gally_version}"
            cpu    = "0.5"
            memory = "1Gi"

        }
        container {
            name   = "nginx"
            image  = "${azurerm_container_registry.cr.login_server}/gally/gally-php:${var.gally_version}"
            cpu    = "1"
            memory = "2Gi"

        }
        max_replicas = 1
        min_replicas = 1
    }
}


