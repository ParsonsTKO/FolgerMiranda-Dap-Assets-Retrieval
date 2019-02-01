# Load custom setitngs
UNAME := $(shell uname)
-include .env.mk
provision ?= docker
include etc/$(provision)/makefile

ifdef command
override command := -c "$(command)"
endif

compose = docker-compose
container := app
image=folgerdap/assetsretrieval/$(container)
registryurl=159895783284.dkr.ecr.us-east-2.amazonaws.com/folgerdap/assetsretrieval/$(container)

ecslogin:
ifdef profile
	`aws ecr get-login --no-include-email --region us-east-2 --profile ${profile}`
else
	`aws ecr get-login --no-include-email --region us-east-2`
endif

tag:
	git tag -a $(version) -m "Version $(version)"
	git push origin $(version)

deploy: ## Login to Registry, build, tag and push the images. Registry authentication required. Usage: make deploy version="<semver>"
	make ecslogin
	docker build --build-arg SYMFONY_ENV=prod -t $(registryurl):$(version) -f etc/docker/$(container)/Dockerfile .
	docker push $(registryurl):$(version)

deploylatest: ## Login to Registry, build, tag with the latest images and push to registry. Registry authentication required. Usage: make deploylatest version="<semver>"
	docker tag $(registryurl):$(version) $(registryurl):latest
	docker push $(registryurl):latest

publish: ## Tag and deploy version. Registry authentication required. Usage: make publish version="<semver>"
	make tag version=$(version)
	make deploy version=$(version)

preview: ## Tag and deploy a image of the current branch. Registry authentication required. Usage: make preview
	make deploy version=$(branch)

push: ## Push changes, using commitizen. Usage: make push
	git add -A .
	git cz -a
	git pull
	git push -u origin $(shell git rev-parse --abbrev-ref HEAD)

build:
	$(compose) up --build

down:
	$(compose) down

remove:
	$(compose) rm --force

run:
	$(compose) run --rm $(container) /bin/ash $(command)

sync: ## Copy files generated inside container to host
	make run c="cp composer.lock /tmp/app/."
	make run c="cp -r vendor /tmp/app/."

help: ## This help.
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help
.PHONY: all
