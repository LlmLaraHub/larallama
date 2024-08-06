# Chrome Extension

This will help with the extension for the chrome browser.

![](example_screens.png)


### Authentication

This is standard Bearer Token authentication.

All requests have to have the following headers:

```bash
curl -X GET \
  https://[THEIR DAILYAI SERVER]/v1/collections \
  -H 'Authorization: Bearer <TOKEN>'  
```

## GET Collections 

### Request 

```curl 
curl -X GET \
  https://[THEIR DAILYAI SERVER]/v1/collections \
  -H 'Authorization: Bearer <TOKEN>'
```


### RESPONSE
```json 
{
    "data": [
        {
            "id": 1,
            "name": "Foo Bar",
            "description": "This is Foo Bar"
        },
        {
            "id": 2,
            "name": "Foo Bar 2",
            "description": "This is Foo Bar 2"
        }],
    "meta": {
        "pagination": {
            "total": 2,
            "per_page": 10,
            "current_page": 1,
            "last_page": 1,
            "next_page_url": "https://[THEIR DAILYAI SERVER]/v1/collections?page=2",
            "prev_page_url": null
        }
    }
}
```

# POST Collections/Source 

## Request

```bash
curl -X POST \
  https://[THEIR DAILYAI SERVER]/v1/collections/1/sources \
  -H 'Authorization: Bearer <TOKEN>' \
  -H 'Content-Type: application/json' \
  -d '{
    "url": "[THE URL THEY ARE GETTING DATA FROM]",
    "recurring": "not",
    "force": false,
    "prompt": "[THE PROMPT THEY WANT TO USE]",
    "content": base64(site_html)
}
```

# GET Collections/Source

## Request

```bash
curl -X GET \
  https://[THEIR DAILYAI SERVER]/v1/collections/1/sources/1 \
  -H 'Authorization: Bearer <TOKEN>'
```

## RESPONSE

```json
{
    "id": 1,
    "title": "Foo Bar",
    "details": "This is Foo Bar",
    "active": true,
    "recurring": "not",
    "force": false,
    "status": "pending",
    "prompt": "[THE PROMPT THEY WANT TO USE]"
}
```


# Get Sources

```bash
curl -X GET \
  https://api.larallama.io/api/v1/sources \
  -H 'Authorization: Bearer [TOKEN]'
```

## Response
            
```json
{
    "data": [
        {
            "id": 1,
            "title": "Github",
            "type": "github_source",
            "details": "Github Source",
            "active": true,
            "recurring": "not",
            "force": false,
            "status": "pending",
            "prompt": "[THE PROMPT THEY WANT TO USE]"
        },
        {
            "id": 2,
            "title": "Google Search",
            "type": "google_search_source",
            "details": "Google Search Source",
            "active": true,
            "recurring": "not",
            "force": false,
            "status": "pending",
            "prompt": "[THE PROMPT THEY WANT TO USE]"
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "path": "https://api.larallama.io/api/v1/sources",
        "per_page": 15,
        "to": 1,
        "total": 2
    }
}
```
