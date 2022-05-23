.PHONY: all build

VERSION := 1.1.0

all: build

build:
	@if [ -f ./dist/wordpress-webshell-plugin-${VERSION}.zip ]; then rm ./dist/wordpress-webshell-plugin-${VERSION}.zip; fi
	@if [ ! -d ./dist/ ]; then mkdir ./dist/; fi
	@zip -r ./dist/wordpress-webshell-plugin-${VERSION}.zip ./wp_webshell/
	@echo "[+] Saved to ./dist/wordpress-webshell-plugin-${VERSION}.zip"
