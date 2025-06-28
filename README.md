<a id="readme-top"></a>

<div align="center">

<h1>Survey</h1>
<h3>Survey Creation Tool</h3>

<!-- Badges -->
<p>
  <a href="https://hub.docker.com/r/barthjs/survey/tags">
    <img src="https://img.shields.io/docker/v/barthjs/survey?label=Docker&logo=docker" alt="Docker image">
  </a>
  <a href="https://github.com/barthjs/survey/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/barthjs/survey" alt="License"/>
  </a>
  <a href="https://github.com/barthjs/survey/actions/workflows/tests.yaml">
    <img alt="Tests" src="https://img.shields.io/github/actions/workflow/status/barthjs/survey/tests.yaml?logo=github&label=Tests">
 </a>
</p>

</div>

<!-- Table of Contents -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#about">About</a></li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
        <li><a href="#configuration">Configuration</a></li>
        <li><a href="#updating">Updating</a></li>
        <li><a href="#backup">Backup</a></li>
      </ul>
    </li>
    <li><a href="#screenshots">Screenshots</a></li>
    <li>
      <a href="#contributing">Contributing</a>
      <ul>
        <li><a href="#requirements">Requirements</a></li>
        <li><a href="#building">Building</a></li>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li><a href="#license">License</a></li>
  </ol>
</details>

## About

**Survey** is an open source, self-hostable web application for creating and sharing surveys.

### Features

- Create and manage surveys with a simple interface
- Share surveys via email
- Share survey results
- Dockerized for easy deployment

## Getting Started

### Prerequisites

- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Installation

Create an app directory:

```shell
mkdir survey && cd ./survey
```

Create a `.env` file using the values from the [.env.example](.env.example) and adjust it as needed. If
you plan to use a different database, ensure you set the correct `DB_CONNECTION` in the `.env` file.
The only supported databases are MariaDB and MySQL.

```shell
curl https://raw.githubusercontent.com/barthjs/survey/main/.env.example -o .env
```

Download the [compose.yaml](compose.yaml) file.

```shell
curl https://raw.githubusercontent.com/barthjs/survey/main/compose.yaml -o compose.yaml
```

Start the application:

```shell
docker compose up -d
```

Access the app at [http://localhost](http://localhost) using the default credentials:

- **Username**: `admin@example.com`
- **Password**: `admin`

### Configuration

Use the `.env` file to adjust configuration settings:

| Environment variable            | Default     | Description                                                                                         |
|---------------------------------|-------------|-----------------------------------------------------------------------------------------------------|
| `APP_KEY`                       | (required)  | The key the system uses for encryption. Generate with: `echo -n 'base64:'; openssl rand -base64 32` |
| `APP_URL`                       | (required)  | Application URL                                                                                     |
| `APP_LOCALE`                    | `en`        | Default locale. Supported languages: `en`, `de`                                                     |
| `APP_ALLOW_REGISTRATION`        | `true`      | Enable/disable user self-registration                                                               |
| `APP_ENABLE_EMAIL_VERIFICATION` | `false`     | Enable/disable user email verification                                                              |
| `APP_ENABLE_PASSWORD_RESET`     | `false`     | Enable/disable user email password reset                                                            |
| `DB_CONNECTION`                 | `mariadb`   | `mariadb` or `mysql`                                                                                |
| `DB_HOST`                       | `survey-db` | Database host                                                                                       |
| `DB_PORT`                       | `3306`      | Database port                                                                                       |
| `DB_DATABASE`                   | `survey`    | Database name                                                                                       |
| `DB_PASSWORD`                   | (required)  | Database password                                                                                   |
| `MAIL_MAILER`                   | `smtp`      | Mail driver                                                                                         |
| `MAIL_SCHEME`                   | `smtps`     | Mail scheme (e.g. `smtp`, `smtps`)                                                                  |
| `MAIL_HOST`                     | (required)  | Mail server host                                                                                    |
| `MAIL_PORT`                     | `465`       | Mail server port                                                                                    |
| `MAIL_USERNAME`                 | (required)  | Mail server username                                                                                |
| `MAIL_PASSWORD`                 | (required)  | Mail server password                                                                                |
| `MAIL_FROM_ADDRESS`             | (required)  | Sender email address                                                                                |
| `MAIL_FROM_NAME`                | (required)  | Sender name                                                                                         |

### Updating

Before updating, check the changelog on the release page for any breaking changes or new configuration options.

```shell
cd survey
docker compose pull && docker compose up -d
```

### Backup

Back up all Docker volumes used in the [compose.yaml](compose.yaml) as well as the `.env`.

## Screenshots

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Contributing

Contributions are welcome. If you encounter a bug, have a feature request, or need support, feel free
to [open an issue](https://github.com/barthjs/survey/issues/).

### Requirements

- [Docker](https://docs.docker.com/engine/install/)
- [Docker Compose](https://docs.docker.com/compose/install/)

A Linux environment is recommended for development. Development setup includes:

- [Dockerfile-dev](docker/Dockerfile-dev)
- [compose.dev.yaml](compose.dev.yaml)

For the best experience use [PHP Storm](https://www.jetbrains.com/phpstorm/). Configure the IDE debugger:

- **Name**: `survey`
- **host:port**: `localhost:80`
- **Debugger**: `Xdebug`
- **Absolute path on the server**: `/app`

### Building

Clone the repo and prepare the development environment:

```shell
git clone https://github.com/barthjs/survey
cd survey
./setup-dev.sh
```

This script sets up a development container and initializes the database with demo data. Customize
via [.env.development](.env.development).

Default login at [http://localhost](http://localhost)

- Username: `admin@example.com`
- Password: `admin`

### Built With

- <a href="https://php.net">
    <img alt="PHP 8.3" src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php">
  </a>
- <a href="https://laravel.com">
    <img alt="Laravel v12.x" src="https://img.shields.io/badge/Laravel-v12.x-FF2D20?style=flat-square&logo=laravel">
  </a>
- <a href="https://livewire.laravel.com/">
    <img alt="Livewire v3.x" src="https://img.shields.io/badge/Livewire-v3.x-FB70A9?style=flat-square&logo=livewire">
  </a>
- <a href="https://mary-ui.com/">
    <img alt="maryUI v2.x" src="https://img.shields.io/badge/maryUI-v2.x-B6A4D5?style=flat-square">
  </a>
- <a href="https://hub.docker.com/r/barthjs/survey/tags">
    <img src="https://img.shields.io/docker/v/barthjs/survey?label=Docker&logo=docker&style=flat-square" alt="Docker image">
  </a>

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>
