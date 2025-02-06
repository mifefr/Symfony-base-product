[![Coverage Status](https://coveralls.io/repos/github/mifefr/base-product/badge.svg?branch=master)](https://coveralls.io/github/mifefr/base-product?branch=master)
# Base Product Template 

This project serves as a template for building product-related applications using Symfony. It showcases an example of architecture leveraging Hexagonal Architecture, CQRS, and Stripe integration.

## Project Overview

- **Framework**: Symfony
- **Architecture**: Hexagonal Architecture
- **Patterns**: CQRS (Command Query Responsibility Segregation)
- **Payment Integration**: Stripe

## Features

- **Hexagonal Architecture**: Promotes separation of concerns and decouples the core logic from external systems.
- **CQRS**: Separates read and write operations to optimize performance and scalability.
- **Stripe Integration**: Demonstrates payment processing capabilities using Stripe's API.

## Getting Started

### Prerequisites

- Docker
- Docker Compose

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/mifefr/Symfony-base-product
   cd base-product
   ```

2. Set up environment variables:
   - Copy `.env.example` to `.env` and configure your environment variables, including `STRIPE_SECRET_KEY`.

3. Build and start the Docker containers:
   ```bash
   docker-compose up --build -d
   ```

4. Access the application:
   - Web: [http://localhost:8080](http://localhost:8080)
   - MySQL: Host `localhost`, Port `3306`

### Running Tests

Execute the following command to run the test suite:
```bash
php bin/phpunit
```

## Usage

- **Create Product**: Use the `POST /api/products` endpoint to create new products.
- **List Products**: Use the `GET /api/products` endpoint to retrieve all products.
- **Get Product**: Use the `GET /api/products/{id}` endpoint to retrieve product details.
- **Create Payment**: Use the `/api/payment/create` endpoint to initiate a payment.
- **Check Payment Status**: Use the `/api/payment/{paymentId}/status` endpoint to check payment status.

## License

This project is licensed under the MIT License.

## Contributing

Contributions are welcome :) Please open an issue or submit a pull request for any changes.
