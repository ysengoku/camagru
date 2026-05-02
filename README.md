# camagru

## Architecture

### MVC (Model-View-Controller)

This repository contains a PHP MVC application used for the Camagru project.

## Development workflow

The application is built and run with Docker.

- `make dev` builds the development image and starts the stack
- Composer dependencies are installed during the development image build
- `app/vendor/` is generated inside Docker and ignored by git

## PHP linting

This project uses PHP_CodeSniffer for linting and automatic fixing.

```sh
make lint-php
make fix-php
```

You can also run the Composer scripts inside the running app container:

```sh
docker exec camagru_app composer run lint
docker exec camagru_app composer run fix
```

The lint rules are defined in [app/phpcs.xml](app/phpcs.xml) and are intentionally relaxed for this codebase's global-namespace structure.
