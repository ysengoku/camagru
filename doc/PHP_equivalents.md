# Node.js - PHP Standard Library Equivalents

This document lists Node.js functions, classes, or methods used in this project and their equivalents in PHP standard library, along with a brief description.

## Server / HTTP

| Node.js               | PHP                                                    | Description |
|-----------------------|--------------------------------------------------------|-------------|
| `http.createServer()` | Built-in PHP server (`php -S`) or `$_SERVER` + routing | Creates an HTTP server to handle requests. |

## URL / Query

| Node.js                  | PHP           | Description |
|--------------------------|---------------|-------------|
| `new URL(req.url, base)` | `parse_url()` | Parses a URL string into components. |
| `URL.searchParams`       | `parse_str()` | Parses a query string into key-value pairs. |

## Data / Encoding

| Node.js                          | PHP                     | Description |
|----------------------------------|-------------------------|-------------|
| `bcrypt.hash()`                  | `password_hash()`       | Creates a hash of the password. |
| `bcrypt.compare()`               | `password_verify()`     | Compare user input pqssword and hashed password. |
| `randomBytes()`                  | `random_bytes()`        | Generate a cryptographically secure 32-byte random value. |
| `JSON.parse()`                   | `json_decode()`         | Parses a JSON string into an object or array. |
| `JSON.stringify()`               | `json_encode()`         | Converts an object or array into a JSON string. |


## Database

| Node.js               | PHP   | Description |
|-----------------------|-------|-------------|
| `mysql2.createPool()` | `PDO` | Creates a pool of database connections. |

## File System

| Node.js              | PHP                   | Description |
|----------------------|-----------------------|-------------|
| `fs.readFileSync()`  | `file_get_contents()` | Reads the entire contents of a file synchronously. |
| `fs.writeFileSync()` | `file_put_contents()` | Writes data to a file synchronously, replacing existing content. |

## Logging / Debug

| Node.js           | PHP                                 | Description |
|-------------------|-------------------------------------|-------------|
| `console.log()`   | `echo` / `print_r()` / `var_dump()` | Prints messages to standard output. |
| `console.error()` | `error_log()`                       | Logs an error message. |
