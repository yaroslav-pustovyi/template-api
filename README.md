# Template API

A REST API for managing templates built with Symfony 7.3 and MongoDB ODM.

## Prerequisites

- Docker and Docker Compose
- Git

## Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/yaroslav-pustovyi/template-api.git
   cd template-api
   ```

2. **Build and start the application**
   ```bash
   docker-compose up -d
   ```

3. **Install dependencies**
   ```bash
   docker-compose exec php composer install
   ```

4. **Load sample data**
   ```bash
   docker-compose exec php php bin/console doctrine:mongodb:fixtures:load --dm=default
   ```

The API will be available at `http://localhost:8080`

## Services

- **Nginx**: Web server (port 8080)
- **PHP-FPM**: Application server
- **MongoDB**: Database (port 27017)

## API Endpoints

### List Templates

**GET** `/v1/templates`

Lists all templates with pagination support. Optionally filter by category.

**Query Parameters:**
- `categoryId` (optional): Filter templates by category ID
- `page` (optional): Page number (default: 1)
- `perPage` (optional): Items per page (default: 20, max: 100)

**Example Request:**
```bash
# List all templates
curl -X GET "http://localhost:8080/v1/templates"

# List templates with pagination
curl -X GET "http://localhost:8080/v1/templates?page=1&perPage=10"

# Filter by category
curl -X GET "http://localhost:8080/v1/templates?categoryId=675d123456789abcdef12345"
```

**Example Response:**
```json
{
  "data": [
    {
      "id": "675d123456789abcdef12346",
      "name": "Cartoon Avatar",
      "displayName": "Cartoon Avatar",
      "preview": {
        "aspectRatio": 1.0,
        "imageURL": "https://example.com/preview.png"
      },
      "templateData": {
        "aiFilter": {
          "aiFilter": {
            "userPrompt": {
              "title": "Create your avatar",
              "inputFields": [
                {
                  "id": "field1",
                  "type": "textField",
                  "caption": "Your style",
                  "placeholder": "Cartoon, Realistic",
                  "maxLength": 100
                }
              ]
            }
          }
        }
      },
      "category": {
        "id": "675d123456789abcdef12345",
        "name": "Cartoon",
        "displayName": "Cartoon"
      }
    }
  ],
  "meta": {
    "page": 1,
    "perPage": 20,
    "total": 15,
    "totalPages": 1,
    "hasNext": false,
    "hasPrevious": false
  }
}
```

### Create Template

**POST** `/v1/templates`

Creates a new template.

**Request Body:**
```json
{
  "name": "My New Template",
  "displayName": "My New Template Display Name",
  "categoryId": "675d123456789abcdef12345",
  "preview": {
    "aspectRatio": 1.0,
    "imageURL": "https://example.com/preview.png"
  },
  "templateData": {
    "type": "custom",
    "settings": {
      "option1": "value1"
    }
  }
}
```

**Example Request:**
```bash
curl -X POST "http://localhost:8080/v1/templates" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "AI Portrait Generator",
    "displayName": "AI Portrait Generator",
    "categoryId": "675d123456789abcdef12345",
    "preview": {
      "aspectRatio": 1.0,
      "imageURL": "https://example.com/ai-portrait-preview.png"
    },
    "templateData": {
      "aiFilter": {
        "aiFilter": {
          "userPrompt": {
            "title": "Generate your portrait",
            "inputFields": [
              {
                "id": "style_field",
                "type": "textField",
                "caption": "Art style",
                "placeholder": "Realistic, Abstract, Watercolor",
                "maxLength": 150
              }
            ]
          }
        }
      }
    }
  }'
```

**Success Response (201):**
```json
{
  "id": "675d123456789abcdef12347",
  "name": "AI Portrait Generator",
  "displayName": "AI Portrait Generator",
  "preview": {
    "aspectRatio": 1.0,
    "imageURL": "https://example.com/ai-portrait-preview.png"
  },
  "templateData": {
    "aiFilter": {
      "aiFilter": {
        "userPrompt": {
          "title": "Generate your portrait",
          "inputFields": [
            {
              "id": "style_field",
              "type": "textField",
              "caption": "Art style",
              "placeholder": "Realistic, Abstract, Watercolor",
              "maxLength": 150
            }
          ]
        }
      }
    }
  },
  "category": {
    "id": "675d123456789abcdef12345",
    "name": "Cartoon",
    "displayName": "Cartoon"
  }
}
```

**Validation Error Response (400):**
```json
{
  "error": "Validation failed",
  "violations": {
    "name": "Name is required",
    "categoryId": "Category ID is required"
  }
}
```

**Business Logic Error Response (400):**
```json
{
  "error": "Template with name 'AI Portrait Generator' already exists"
}
```

### Delete Template

**DELETE** `/v1/templates/{id}`

Deletes a template by ID.

**Example Request:**
```bash
curl -X DELETE "http://localhost:8080/v1/templates/675d123456789abcdef12347"
```

**Success Response (204):** Empty response body

**Not Found Response (404):** Empty response body

## Data Model

### Template
- `id`: Unique identifier
- `name`: Unique template name (max 100 characters, required)
- `displayName`: Display name (max 150 characters, required)
- `categoryId`: Reference to category (required)
- `preview`: Array containing preview data (aspectRatio, imageURL, etc.)
- `templateData`: Array containing template configuration data
- `createdAt`: Creation timestamp
- `updatedAt`: Last update timestamp

### Category
- `id`: Unique identifier
- `name`: Category name (max 50 characters, required)
- `displayName`: Category display name (max 100 characters, required)
- `createdAt`: Creation timestamp
- `updatedAt`: Last update timestamp

## Development Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f php

# Access PHP container
docker-compose exec php bash

# Install dependencies
docker-compose exec php composer install

# Run tests
docker-compose exec php php bin/phpunit

# Load fixtures
docker-compose exec php php bin/console doctrine:mongodb:fixtures:load --dm=default

# Clear cache
docker-compose exec php php bin/console cache:clear
```

## Database Access

MongoDB is available at:
- **Host**: localhost
- **Port**: 27017
- **Database**: template_api
- **Username**: app_user
- **Password**: app_password

## Testing

The project includes comprehensive unit tests for the service layer:

```bash
# Run all tests
docker-compose exec php php bin/phpunit

# Run specific test file
docker-compose exec php php bin/phpunit tests/Unit/Service/TemplateServiceTest.php

# Run with coverage (if configured)
docker-compose exec php php bin/phpunit --coverage-text
```
