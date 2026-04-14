# BookShelf API

Companion project for the book **"Laravel REST APIs: A Practical Guide"**.

> ⚠️ This is a **learning project**, not a standalone application. The code is meant to be built step by step, following the chapters of the book. Each chapter adds a feature, from `composer create-project` all the way to a fully tested, documented and deploy-ready REST API.

## Stack

- PHP 8.3+ / Laravel 13
- SQLite (development)
- Sanctum (token authentication)
- Pest (testing)
- Scribe (API documentation)
- Envoy (deploy)

## Entities

- **Book** — title, ISBN, description, publication year, language, cover image
- **Author** — one author, many books
- **Genre** — many-to-many with books
- **User** — authenticated via Sanctum tokens
- **Review** — rating and comment on a book, with edit time window

## Endpoints

**Public:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/books | List books (filter, sort, paginate) |
| GET | /api/books/{book} | Get a book |
| GET | /api/books/{book}/reviews | List reviews for a book |
| GET | /api/books/{book}/reviews/{review} | Get a review |
| GET | /api/authors | List authors |
| GET | /api/authors/{author} | Get an author |
| GET | /api/authors/{author}/books | List books by author |
| GET | /api/genres | List genres |
| POST | /api/auth/register | Register |
| POST | /api/auth/login | Login |

**Authenticated:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | /api/auth/logout | Logout (current token) |
| POST | /api/auth/logout/all | Logout (all tokens) |
| GET | /api/auth/me | Current user |
| POST | /api/books | Create a book |
| PUT | /api/books/{book} | Update a book (owner only) |
| DELETE | /api/books/{book} | Delete a book (owner only) |
| POST | /api/books/{book}/cover | Upload cover |
| DELETE | /api/books/{book}/cover | Delete cover |
| POST | /api/books/{book}/reviews | Create a review |
| PUT | /api/books/{book}/reviews/{review} | Update a review (owner, time-limited) |
| DELETE | /api/books/{book}/reviews/{review} | Delete a review (owner, time-limited) |

## Local setup

```bash
git clone https://github.com/gitantonio/bookshelf.git
cd bookshelf
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

To generate the API documentation:

```bash
php artisan scribe:generate
```

Then open `http://localhost:8000/docs`.

## Tests

```bash
php artisan test
```

## License

This project is provided as educational material for readers of the book.
