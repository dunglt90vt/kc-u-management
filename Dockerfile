FROM dunglas/frankenphp:1-alpine AS frankenphp_upstream
FROM composer/composer:2-bin AS composer_upstream

# Base FrankenPHP image
FROM frankenphp_upstream AS frankenphp_base

WORKDIR /srv/web

RUN apk add --no-cache npm \
    && apk add --no-cache patch

RUN install-php-extensions \
	pdo_mysql \
	gd \
	intl \
	zip \
	opcache \
	apcu

COPY infra/conf.d/app.ini $PHP_INI_DIR/conf.d/
COPY --chmod=755 infra/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY infra/Caddyfile /etc/caddy/Caddyfile

ENTRYPOINT ["docker-entrypoint"]
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=composer_upstream /composer /usr/bin/composer

# use the official Bun image
# see all versions at https://hub.docker.com/r/oven/bun/tags
FROM oven/bun:1 AS base_bun
WORKDIR /srv/app

# install dependencies into temp directory
# this will cache them and speed up future builds
FROM base_bun AS bun_dev
RUN mkdir -p /temp/dev
COPY app/package.json app/bun.lockb /temp/dev/
RUN cd /temp/dev && bun install --frozen-lockfile

# install with --production (exclude devDependencies)
RUN mkdir -p /temp/prod
COPY app/package.json app/bun.lockb /temp/prod/
RUN cd /temp/prod && bun install --frozen-lockfile --production

# copy node_modules from temp directory
# then copy all (non-ignored) project files into the image
FROM base_bun AS prerelease
COPY --from=bun_dev /temp/dev/node_modules node_modules
COPY . .

# [optional] tests & build
ENV NODE_ENV=production
RUN bun test
RUN bun run build

# copy production dependencies and source code into final image
FROM base_bun AS release
COPY --from=bun_dev /temp/prod/node_modules node_modules
COPY --from=prerelease /app/app/index.ts .
COPY --from=prerelease /app/app/package.json .
