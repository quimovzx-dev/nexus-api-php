# NEXUS API 🐘

[![CI](https://github.com/quimovzx-dev/nexus-api-php/actions/workflows/ci.yml/badge.svg)](https://github.com/quimovzx-dev/nexus-api-php/actions/workflows/ci.yml) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A dependency-light PHP REST API backed by SQLite, with password hashing and bearer-token authentication.

## Endpoints
| Method | Route | Purpose |
|---|---|---|
| GET | `/health` | Service health |
| POST | `/register` | Create a user |
| POST | `/login` | Authenticate and issue a token |
| GET | `/me` | Read the authenticated user |

## Run
```bash
php -S localhost:8080 -t public
```

The API creates `data/nexus.sqlite` automatically. Passwords use PHP's `password_hash`; bearer tokens are stored as SHA-256 hashes.

## CI
GitHub Actions performs PHP syntax validation and an API smoke test against PHP's built-in server.

## License
MIT
