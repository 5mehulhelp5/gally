locals {
  condition_alaphanumeric_regex         = "^[a-zA-Z0-9]+$"
  condition_alaphanumeric_error_message = "Alpha numeric characters only are allowed in '%s'"
}

variable "gally_prefix" {
  description = "Prefix used on resource names (gally by default)"
  type        = string
  default     = "gally"

  validation {
    condition     = can(regex(local.condition_alaphanumeric_regex, var.gally_prefix))
    error_message = format(local.condition_alaphanumeric_error_message, "gally_prefix")
  }
}

variable "project_name" {
  description = "Your project name (shou)"
  type        = string

  validation {
    condition     = can(regex(local.condition_alaphanumeric_regex, var.project_name))
    error_message = format(local.condition_alaphanumeric_error_message, "project_name")
  }
}

variable "location" {
  description = "The Azure Region where the resources should exist"
  type        = string
}

variable "gally_version" {
  description = "Gally version to deploy"
  type        = string
  default     = "2.0"
}

variable "postgres_version" {
  description = "Postgres version to deploy"
  type        = string
  default     = "16"
}

variable "redis_version" {
  description = "Redis version to deploy"
  type        = string
  default     = "6.2"
}

variable "composer_auth" {
  description = "Composer auth"
  type        = object({})
  default     = {}
}

