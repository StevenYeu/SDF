# SDF Catalog

The catalog will facilitate the discovery, dissemination, and reuse of NSF funded products, and help in reporting on the evaluation metrics as defined by the product owners.

The catalog is based on the [SciCrunch Portal](https://github.com/SciCrunch/SciCrunch-Portal) and has been modified to work with SDF specific data

## Changes from SciCrunch
- Changed search functionality to search local database instance of external solar search service
- Added CILogon integration
- Updated Relationships view correctly display relationships for each resource
- Change default community to SDF community
- Changed email service to use UCSD email server instead of mailgun
- Made SDF specific components to display resources


## CILogon Setup
Please follow the [CILogon Instructions](https://www.cilogon.org/oidc) on how to integrate it with this application

## OSC Integration
This [repo](https://github.com/OpenScienceChain/Catalog-CLU) has the scripts that sends resources from SDF to OSC and block chain.

## How to run 

### Prerequisites  
- Domain name
- SSL certs 
- Docker

The current configuration expects SSL certs and a domain name. 
NOTE: some of the links in source code may reference SDF domain name. That will need to change your own domain name. The `config.php` will need to be updated with the domain name too.

### Run app using Docker
1. Copy `docker-compose-example.yml` to a new file called `docker-compose.yml`
2. Replace all of the `FILL_IN`  with the appropriate values and update the SSL cert location.
3. Run `docker-compose up -d` (or `docker compose up -d` depending on the version of docker) 
