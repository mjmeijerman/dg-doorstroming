SHELL=/bin/bash

.DEFAULT_GOAL := help

## up: Runs vagrant up
.PHONY: up
up:
	vagrant up

## destroy: Destroys the project
.PHONY: destroy
destroy:
	vagrant destroy

## deploy:	Deploy the code to remote environments
.PHONY: deploy
deploy:
	./deploy

## release:	Make a new release
.PHONY: release
release:
	bin/check-uncommitted-changes.sh
	git checkout master
	git pull
	bin/bump-tag.sh
	git push origin master && git push --tags
	./deploy

## branch:	Make a new branch
.PHONY: branch
branch:
	git checkout master
	git pull
	@read -p "Enter Branch Name: " branchName; \
    git checkout -b $$branchName

## help:		Print this message
.PHONY: help
help: Makefile
	@sed -n 's/^##//p' $<
