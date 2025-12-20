<div align="center">

<h1>Survey</h1>
<h3>Survey Creation Tool</h3>

<p>
  <a href="https://github.com/barthjs/survey/releases">
    <img src="https://img.shields.io/github/v/release/barthjs/survey?sort=semver&label=version" alt="GitHub Release">
  </a>
  <a href="https://github.com/barthjs/survey/blob/main/LICENSE">
    <img src="https://img.shields.io/github/license/barthjs/survey" alt="License">
  </a>
</p>

</div>

<hr>

<a id="readme-top"></a>

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li><a href="#about">About</a></li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#configuration">Configuration</a></li>
    <li><a href="#screenshots">Screenshots</a></li>
    <li><a href="#updating">Updating</a></li>
    <li><a href="#backup">Backup</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#development">Development</a></li>
    <li><a href="#built-with">Built With</a></li>
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

1. Create an app directory:

    ```shell
    mkdir -p /opt/survey
    cd /opt/survey
    ```

2. Create a `.env` file using the values from the [.env.example](.env.example) and adjust it as needed. If
   you plan to use a different database, ensure you set the correct `DB_CONNECTION` in the `.env` file.

    ```shell
    curl https://raw.githubusercontent.com/barthjs/survey/main/.env.example -o .env
    ```

3. Download the [compose.yaml](compose.yaml) file.

    ```shell
    curl https://raw.githubusercontent.com/barthjs/survey/main/compose.yaml -o compose.yaml
    ```

4. Start the application:

    ```shell
    docker compose up -d
    ```

5. Log in at  [http://localhost](http://localhost) using the default credentials:

    - **Username**: `admin`
    - **Password**: `admin`

## Configuration

Use the `.env` file to adjust configuration settings:

| Environment variable            | Default     | Description                                                                                       |
|---------------------------------|-------------|---------------------------------------------------------------------------------------------------|
| `APP_KEY`                       | (required)  | Key used to encrypt and decrypt data. Generate with: `echo -n 'base64:'; openssl rand -base64 32` |
| `APP_URL`                       | (required)  | Application URL for notifications                                                                 |
| `APP_LOCALE`                    | `en`        | Default locale. Supported languages: `en`, `de`                                                   |
| `APP_ALLOW_REGISTRATION`        | `false`     | Enable/disable user self-registration                                                             |
| `APP_ENABLE_EMAIL_VERIFICATION` | `false`     | Enable/disable user email verification                                                            |
| `APP_ENABLE_PASSWORD_RESET`     | `false`     | Enable/disable user email password reset                                                          |
| `LOG_CHANNEL`                   | `stdout`    | `stdout` logs to Docker, whereas `file` writes to `/app/storage/survey.log`                       |
| `LOG_LEVEL`                     | `warning`   | Log level: `debug`, `info`, `warning`, `error`                                                    |
| `DB_CONNECTION`                 | `pgsql`     | Database driver: `pgsql`, `mariadb` or `sqlite`,                                                  |
| `DB_HOST`                       | `survey-db` | Database host name                                                                                |
| `DB_PORT`                       | `5432`      | Database port (`5432` for pgsql, `3306` for mariadb).                                             |
| `DB_DATABASE`                   | `survey`    | Database name (for `sqlite`, the path to the database file).                                      |
| `DB_USERNAME`                   | `survey`    | Database username                                                                                 |
| `DB_PASSWORD`                   | (required)  | Database password                                                                                 |
| `MAIL_MAILER`                   | `smtp`      | Mail driver                                                                                       |
| `MAIL_SCHEME`                   | `smtps`     | Mail scheme (e.g. `smtp`, `smtps`)                                                                |
| `MAIL_HOST`                     | (required)  | Mail server host                                                                                  |
| `MAIL_PORT`                     | `465`       | Mail server port                                                                                  |
| `MAIL_USERNAME`                 | (required)  | Mail server username                                                                              |
| `MAIL_PASSWORD`                 | (required)  | Mail server password                                                                              |
| `MAIL_FROM_ADDRESS`             | (required)  | Sender email address                                                                              |
| `MAIL_FROM_NAME`                | (required)  | Sender name                                                                                       |

## Screenshots

<p align="right">(<a href="#readme-top">back to top</a>)</p>

## Updating

Before updating, check the changelog on the release page for any breaking changes or new configuration options.

```shell
cd /opt/survey
docker compose pull && docker compose up -d
```

## Backup

Back up all Docker volumes used in the [compose.yaml](compose.yaml) as well as the `.env`.

## Contributing

Contributions are welcome. If you encounter a bug, have a feature request, or need support, feel free
to [open an issue](https://github.com/barthjs/survey/issues/).

Please read the [contributing guidelines](CONTRIBUTING.md) for more details.

## Development

See the [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to setup a development environment.

### Built With

- <a href="https://php.net" target="_blank">
    <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php" alt="PHP 8.5">
  </a>
- <a href="https://laravel.com" target="_blank">
    <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel" alt="Laravel 12">
  </a>
- <a href="https://livewire.laravel.com/" target="_blank">
    <img src="https://img.shields.io/badge/Livewire-3-FB70A9?style=flat-square&logo=livewire" alt="Livewire 3">
  </a>
- <a href="https://alpinejs.dev/" target="_blank">
    <img src="https://img.shields.io/badge/Alpine.js-3-77C1D2?style=flat-square&logo=alpinedotjs" alt="Alpine.js 3">
  </a>
- <a href="https://tailwindcss.com/" target="_blank">
    <img src="https://img.shields.io/badge/TailwindCSS-4-00BCFF?style=flat-square&logo=tailwindcss" alt="TailwindCSS 4">
  </a>
- <a href="https://mary-ui.com/" target="_blank">
    <img src="https://img.shields.io/badge/maryUI-2-B6A4D5?style=flat-square" alt="maryUI 2">
  </a>

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>
