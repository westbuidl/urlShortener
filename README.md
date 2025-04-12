# URL Shortener Service

A simple URL shortening service built with Laravel that converts long URLs into short, manageable links.

- `POST /api/encode`: Converts a long URL to a short URL
- `POST /api/decode`: Converts a short URL back to its original URL


## Prerequisites
- PHP (>= 8.1)
- Composer
- Laravel CLI (optional)

## Features

- URL encoding: Convert long URLs into short URLs
- URL decoding: Get the original URL from a short URL
- Automatic redirection: Short URLs automatically redirect to the original URL
- File-based storage: No database required

## Installation

1. Clone the repository:
   ```bash
   git clone <https://github.com/westbuidl/urlShortener>
   cd url-shortener
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate an application key:
   ```bash
   php artisan key:generate
   ```

5. Start the development server:
   ```bash
   php artisan serve
   ```

## Usage

### API Endpoints

#### 1. Encode a URL

**Endpoint:** `POST /encode`

**Request Body:**
```json
{
    "url": "https://www.example.com"
}
```

**Response:**
```json
{
    "original_url": "https://www.example.com",
    "short_url": "http://short.est/a1b2C3"
}
```

#### 2. Decode a URL

**Endpoint:** `POST /decode`

**Request Body:**
```json
{
    "url": "http://short.est/a1b2C3"
}
```

**Response:**
```json
{
    "short_url": "http://short.est/a1b2C3",
    "original_url": "https://www.example.com"
}
```


## Testing

Run the tests to ensure the functionality works correctly:

```bash
php artisan test
```

## Implementation Details

- **Storage**: The service uses file-based storage (JSON file) to persist URLs between requests
- **Encoding**: Uses Base62 encoding to create short, unique codes
- **Validation**: Validates URLs before encoding to ensure they're properly formatted


## License

[MIT License](LICENSE)