###############################################################################
#                       Generated for Scicrunch.org                           #
###############################################################################

FROM scicrunch/scicrunch-ui:scicrunch_php-fpm 
WORKDIR "/var/www"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y install --no-install-recommends php5-mysql \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \
&& apt-get update && apt-get install -y --no-install-recommends apt-utils
