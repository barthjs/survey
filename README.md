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
    <li><a href="#openid-connect-sso">OpenID Connect (SSO)</a></li>
    <li><a href="#account-management">Account Management</a></li>
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

Survey supports PostgreSQL only. The [compose.yaml](compose.yaml) includes a PostgreSQL container.

### Installation

1. Create an app directory:

    ```shell
    mkdir -p /opt/survey
    cd /opt/survey
    ```

2. Create a `.env` file using the values from the [.env.example](.env.example) and adjust it as needed.

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

5. Log in at [http://localhost:8080](http://localhost:8080) using the default credentials:

    - **Username**: `admin@example.com`
    - **Password**: `admin`

## Configuration

The [.env.example](.env.example) contains all supported settings with their production defaults.
Adjust them in the `.env` file:

| Environment variable            | Default                 | Description                                                                                          |
|---------------------------------|-------------------------|------------------------------------------------------------------------------------------------------|
| `APP_NAME`                      | `Survey`                | Application name shown in the interface and used as email sender name                                |
| `APP_KEY`                       | (required)              | Key used to encrypt and decrypt data. Generate with: `echo -n 'base64:'; openssl rand -base64 32`    |
| `APP_DEBUG`                     | `false`                 | Show detailed error pages. Enable only temporarily for troubleshooting.                              |
| `APP_URL`                       | `http://localhost:8080` | Base URL of the application                                                                          |
| `APP_LOCALE`                    | `en`                    | Default locale. Supported languages: `en`, `de`                                                      |
| `APP_ALLOW_REGISTRATION`        | `true`                  | Enable/disable user self-registration                                                                |
| `APP_ENABLE_EMAIL_VERIFICATION` | `false`                 | Enable/disable user email verification                                                               |
| `APP_ENABLE_PASSWORD_RESET`     | `false`                 | Enable/disable user email password reset                                                             |
| `LOG_CHANNEL`                   | `stdout`                | `stdout` logs to Docker, `file` writes one file per day to `/app/storage/app/logs` and keeps 14 days |
| `LOG_LEVEL`                     | `warning`               | Log level: `debug`, `info`, `warning`, `error`                                                       |
| `DB_CONNECTION`                 | `pgsql`                 | Database driver, only `pgsql` is supported                                                           |
| `DB_HOST`                       | `database`              | Database host name (service or container name in Docker Compose)                                     |
| `DB_PORT`                       | `5432`                  | Database port                                                                                        |
| `DB_DATABASE`                   | `survey`                | Database name                                                                                        |
| `DB_USERNAME`                   | `postgres`              | Database username                                                                                    |
| `DB_PASSWORD`                   | (required)              | Database password                                                                                    |
| `MAIL_MAILER`                   | `log`                   | Mail driver: `smtp` sends emails, `log` writes them to the log                                       |
| `MAIL_SCHEME`                   | (empty)                 | `smtps` for TLS, `smtp` for STARTTLS. If empty, port `465` uses `smtps` and any other port `smtp`    |
| `MAIL_HOST`                     | (empty)                 | Mail server host, required for `smtp`                                                                |
| `MAIL_PORT`                     | (empty)                 | Mail server port, required for `smtp`                                                                |
| `MAIL_USERNAME`                 | (empty)                 | Mail server username                                                                                 |
| `MAIL_PASSWORD`                 | (empty)                 | Mail server password                                                                                 |
| `MAIL_FROM_ADDRESS`             | (empty)                 | Sender email address, required for `smtp`                                                            |
| `MAIL_FROM_NAME`                | `APP_NAME`              | Sender name                                                                                          |
| `SESSION_LIFETIME`              | `43800`                 | Minutes of inactivity until a user is logged out                                                     |
| `PUID`                          | `1000`                  | User id of the user running the app                                                                  |
| `PGID`                          | `1000`                  | Group id of the user running the app                                                                 |

## OpenID Connect (SSO)

Survey supports authentication via OpenID Connect (OIDC). You can configure a generic provider or use
pre-configured integrations for common identity providers.

### Generic Provider

To use a generic OIDC provider, set the following variables in your `.env`. The provider must support OIDC Discovery via
the provided base URL.

```dotenv
OIDC_ENABLED=true
OIDC_URL=https://auth.example.com
OIDC_CLIENT_ID=your-client-id
OIDC_CLIENT_SECRET=your-client-secret
```

### Specialized Providers

Survey includes built-in support for the following identity providers. These follow a consistent environment
variable pattern:

```dotenv
PREFIX_OIDC_ENABLED=true
PREFIX_OIDC_URL=https://auth.example.com
PREFIX_OIDC_CLIENT_ID=your-client-id
PREFIX_OIDC_CLIENT_SECRET=your-client-secret
```

- [Google](https://developers.google.com/identity/openid-connect/openid-connect): Use prefix `GOOGLE`
- [GitHub](https://docs.github.com/en/developers/apps/building-oauth-apps/authorizing-oauth-apps): Use prefix `GITHUB`
- [Authelia](https://www.authelia.com/configuration/identity-providers/openid-connect/clients/): Use prefix `AUTHELIA`
- [Authentik](https://docs.goauthentik.io/add-secure-apps/providers/oauth2/): Use prefix `AUTHENTIK`
- [Gitea](https://docs.gitea.com/development/oauth2-provider): Use prefix `GITEA`
- [Keycloak](https://www.keycloak.org/documentation): Use prefix `KEYCLOAK` (see specific configuration below)

#### Keycloak Configuration

Keycloak requires a realm name in addition to the base URL:

```dotenv
KEYCLOAK_OIDC_ENABLED=true
KEYCLOAK_OIDC_URL=https://auth.example.com
KEYCLOAK_OIDC_REALM=master
KEYCLOAK_OIDC_CLIENT_ID=your-client-id
KEYCLOAK_OIDC_CLIENT_SECRET=your-client-secret
```

### Redirect URI

When configuring your Identity Provider (IdP), you must use the following callback URL:

`https://your-domain.com/auth/oidc/[provider]/callback`

Replace `[provider]` with the identifier of your provider (`google`, `github`,`authelia`, `authentik`, `gitea`,
`keycloak`, or `oidc` for the generic provider).

## Account Management

If you lose access to your account, you can reset your password or disable 2FA via the command line.

### Reset Password

To reset a user's password:

```shell
docker compose exec survey php artisan reset-password
```

### Disable 2FA

To disable two-factor authentication for a user:

```shell
docker compose exec survey php artisan disable-2fa
```

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

See the [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to set up a development environment.

## Built With

- <a href="https://php.net" target="_blank">
    <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php" alt="PHP 8.5">
  </a>
- <a href="https://laravel.com" target="_blank">
    <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel" alt="Laravel 13">
  </a>
- <a href="https://livewire.laravel.com/" target="_blank">
    <img src="https://img.shields.io/badge/Livewire-4-FB70A9?style=flat-square&logo=livewire" alt="Livewire 4">
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
