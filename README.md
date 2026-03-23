# BookShelf API

This is the companion project for the book **"Laravel 13 REST APIs: A Practical Guide"**.

It is a **learning project** — not a standalone application. The code in this repository is meant to be built step by step, following the chapters of the book. Each chapter adds a new piece to the puzzle, from `laravel new bookshelf` all the way to a fully tested, deploy-ready REST API.

## What we build

**BookShelf** is a REST API for managing a book catalog. It covers the kind of real-world problems you would face in a production API: resources with relationships, authentication, permissions, file uploads, filtering, and pagination.

### Entities

- **Book** — the core resource. Title, ISBN, description, publication year, cover image.
- **Author** — book authors. One author, many books.
- **Genre** — literary genres. Many-to-many relationship with books.
- **User** — API users, authenticated via tokens.
- **Review** — book reviews. A user can review a book with a rating and a comment.

## Requirements

- PHP 8.3+
- Composer
- Laravel 13.x

## Getting started

Follow the book. Chapter by chapter, you will set up this project from scratch and build every feature yourself. No code is skipped, no "imagine this is already done" shortcuts.

## License

This project is provided as educational material for readers of the book.
