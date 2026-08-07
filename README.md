# Camagru

<img src="" width="100%" />

<div align="center">
  <em>  
    This project was created as part of the 42 curriculum by <a href="https://github.com/ysengoku">yusengok</a>.
  </em>
  <br /><br />
  <img src="https://img.shields.io/github/commit-activity/t/ysengoku/camagru?style=flat-square&color=9D9E0A" />
  <img src="https://img.shields.io/github/last-commit/ysengoku/camagru?style=flat-square&color=9D9E0A" />
  <img src="https://img.shields.io/badge/coverage-57.88%25-9D9E0A?style=flat-square" />
</div>

## Table of Contents

<details>
<summary>Click to Show / Hide</summary>

- [About](#about)
- [Objectives](#objectives)
- [Features](#features)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [Tech Stack](#tech-stack)
- [Technical Restrictions](#technical-restrictions)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Usage](#usage)
- [Development](#development)
  - [Workflow](#workflow)
  - [Linting & Formatting](#linting--formatting)
  - [Testing](#testing)
- [Notes](#notes)
- [Resources](#resources)
- [AI Usage](#ai-usage)
- [Authors](#authors)
- [License](#license)

</details>

## About

## Objectives

This project's main objective is to build a custom MVC architecture from scratch, without relying on a framework, while working directly with raw SQL for data persistence.   
It also covers image manipulation: webcam capture, canvas-based editing, coordinate mapping between the preview and the final image, server-side compositing (stickers, text, filters), and storage.

## Features

### User Management

- Account creation with username, email, and password, gated behind email verification before the account becomes active
- Login and session-based authentication with username and password
- One-click logout that immediately clears the session
- Forgot-password flow: request a reset link by email, then set a new password via a time-limited token
- Resend verification/reset email if lost or expired (rate-limited)
- Update profile: username, email (with re-verification), password, avatar, and an email-notification toggle
- Email notifications when someone comments on your posts, with an opt-out in profile settings
- Choose an avatar from your own uploaded posts

### Gallery

- Public gallery of all users' posts with infinite-scroll pagination
- Like and comment on posts when logged in, with live-updating counts
- Download own posts
- Delete own comments

### Studio

- Capture a photo directly from the webcam in the browser
- Compose the shot with stickers, custom text, and filters, previewed live before saving
- Upload a user's image instead of using the webcam
- Final image composed server-side, not client-rendered
- Own post history shown on the studio page
- Delete own posts

### Security

- Passwords hashed, never stored in plain text
- Client-side and server-side validation on every form input and file upload
- Protection against XSS via output escaping
- Protection against SQL injection via prepared statements throughout

### Other

- Responsive layout (header, main content, footer) that adapts across screen sizes
- Automatically generated API documentation from PHPDoc comments in the controllers
- Compatible with Firefox and Google Chrome


## Architecture

### MVC (Model-View-Controller)

This repository contains a PHP MVC application used for the Camagru project.

## Project Structure

## Tech Stack

**Languages:**   
<div>   
  <img src="https://img.shields.io/badge/PHP-333333?style=for-the-badge&logo=php&logoColor=777BB4" />
  <img src="https://img.shields.io/badge/JavaScript-333333?style=for-the-badge&logo=javascript&logoColor=F7DF1E" />
  <img src="https://img.shields.io/badge/CSS-333333?style=for-the-badge&logo=css&logoColor=1572B6" />
</div>
<br /> 

**Database:**   

<div>
  <img src="https://img.shields.io/badge/MySQL-333333?style=for-the-badge&logo=mysql&logoColor=4479A1" />
</div>
<br />

**Tools:**   
<div>
  <img src="https://img.shields.io/badge/Composer-333333?style=for-the-badge&logo=composer&logoColor=885630" />
  <img src="https://img.shields.io/badge/Vite-333333?style=for-the-badge&logo=vite&logoColor=646CFF"/>
  <img src="https://img.shields.io/badge/GitHub_Actions-333333?style=for-the-badge&logo=github-actions&logoColor=2088FF" />
</div>
<br />

*Composer is used only for development tooling (linting, formatting and Psalm) with no runtime dependencies.*

<br />

**Infrastructure:**   
<div>
  <img src="https://img.shields.io/badge/Docker-333333?style=for-the-badge&logo=docker&logoColor=2496ED" />
  <img src="https://img.shields.io/badge/Docker%20Compose-333333?style=for-the-badge&logo=docker&logoColor=2496ED" />
  <img src="https://img.shields.io/badge/nginx-333333?style=for-the-badge&logo=nginx&logoColor=009639" />
  <img src="https://img.shields.io/badge/Cloudflare-333333?style=for-the-badge&logo=cloudflare&logoColor=F38020" />
  <img src="https://img.shields.io/badge/MailTrap-333333?style=for-the-badge&logo=mailtrap&logoColor=22D172" />
</div>
<br />

**Development Environment:**   
<div>
  <img src="https://img.shields.io/badge/Ubuntu_22.04_LTS-333333?style=for-the-badge&logo=ubuntu&logoColor=E95420" />
  <img src="https://img.shields.io/badge/macOS_26_Tahoe-333333?style=for-the-badge&logo=apple&logoColor=F0F0F0" />

  <img src="https://img.shields.io/badge/Google%20Chrome_150-333333?style=for-the-badge&logo=GoogleChrome&logoColor=4285F4"/>
  <img src="https://img.shields.io/badge/Brave-333333?style=for-the-badge&logo=Brave&logoColor=FB542B"/>
  <img src="https://img.shields.io/badge/Firefox_148-333333?style=for-the-badge&logo=firefoxbrowser&logoColor=FF7139"/>
</div>


## Technical Restrictions

- Frontend: HTML, CSS, vanilla JavaScript only
- Backend: any language, limited to functions with an equivalent in the PHP standard library
- Containerized deployment: one or more containers, deployable with a single command (Docker Compose or an equivalent)
- Must be free of any security vulnerabilities
- No errors, warnings, or log lines in any console (both server-side and client-side)

## Getting Started

### Prerequisites

### Installation

### Usage

## Development

### Workflow

The application is built and run with Docker.

- `make dev` builds the development image and starts the stack
- Composer dependencies are installed during the development image build

### Linting & Formatting

This project uses (TODO) for linting and automatic fixing.

```sh
# Backend
make lint-php
make format-php

# Frontend
make lint-js
make format-js
```

The lint rules are defined in [app/phpcs.xml](app/phpcs.xml) and are intentionally relaxed for this codebase's global-namespace structure.

### Testing

## Notes

## Resources

## AI Usage

## Authors

## License

