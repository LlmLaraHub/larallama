# LaraLamma.ai (LaraChain v2)

![](docs/images/LaraLamma.png)

[![CI-CD](https://github.com/LlmLaraHub/laralamma/actions/workflows/ci-cd.yml/badge.svg)](https://github.com/LlmLaraHub/laralamma/actions/workflows/ci-cd.yml)


This is a major update to LaraChain. Been working on other projects for the past year and going to do what I can to simplify the setup and foundation so anyone can get going embedding, search, and chatting with their data!

This will slowly be broken into modules you can use in your project but for now it is a standalone project that actually is super easy to setup, more on that below.

The core of this is a vector plugin for PostGres to simplify the setup. (no Docker). This setup will focus on Mac but others in Windows can make pull requests as needed to make it work there. Thanks to Laravel HERD this might even be easier with that since it has PostGres as well.

On top of this is Laravel's amazing Batching system to easily allow us to batch up jobs and manage the amount of jobs hit these LLMs at a time and in what order!

## YouTube

[Overview](https://youtu.be/rj5YQLbWF9U)

[Getting it installed](https://youtu.be/SUwI70h5kVY)

## Getting Started



Setup your local environment. I am going to use HERD to start off with. https://herd.laravel.com/
Because their services cost $ (which is well worth it imo) I will link to this to setup PostGres https://postgresapp.com/. Typically I default to DBEngine https://dbngin.com/ but I could not get the vector driver working. 

Just install the app like any Laravel App. Copy the .env.example to .env 

Make two database is Postgres (use TablePlus https://tableplus.com/ or some other too or cli) callec larachain and one called testing (this is so you do not wipe your database everytime youtest).

>NOTE: Laravel will offer to make the database if it does not exist but not with PostGres yet for me.

Ok then run this in TablePlus:

> NOTE: if you are not using the HERD paid version then download https://dbngin.com and run Redis with that.

```sql
CREATE EXTENSION vector;
```

```php 
cp .env.example .env 
npm install
composer install
```

Update any secrets in the .env file

All set. Now you can migrate some starting data.

```php 
php artisan migrate:fresh --seed
```

Now to see it work:

```bash
php artisan horizon:watch
php artisan reverb:start --debug
npm run dev
```

We need Horizon since Batching is key.

## Update your PHP.ini 

See this video on how:
[See the video](https://www.youtube.com/watch?v=aTuw6W_8CPE)

```
max_execution_time=0
upload_max_filesize=500M
auto_prepend_file=/Applications/Herd.app/Contents/Resources/valet/dump-loader.php
memory_limit=-1
post_max_size = 100M
```

## Which Model????

Keep in mind the size of the model is not the only thing that matters. If you read over [https://ollama.com/library](https://ollama.com/library)
you will see some are better at chat, some at large context, some at code etc. LaraLamma can use multiple models for different jobs.

Looking in the `config/llmdriver.php` you can see the options in there.


## Pulling down the latest code

When you pull down a branch or new code always do the following:

```bash
git fetch
git checkout {branch}
git pull origin {branch}
composer install
npm install
php artisan pennant:purge
php artisan optimize:clear
php artisan migrate
```

Since most changes will be breaking consider starting a new collection (releases will come soon but this is stil 0.0.x)





## Road Map (still in motion)

You can see a lot of it [here](https://github.com/orgs/LlmLaraHub/projects/1)



## Some links for later
And just thinking how to make some of those flows work in a Laravel environment

## Videos

[Laravel as a Retrieval Augmented Generator system using OpenAi, Claude and Ollama NO CODE Larachain!](https://www.youtube.com/watch?v=rj5YQLbWF9U&t=8s)

## Articles 
[Laravel As A Retrieval Augmented Generator System](https://medium.com/@alnutile/laravel-as-a-retrieval-augmented-generator-system-f3afb64f86aa)

[Local LLM and Laravel in 10 minutes with Local LLM Embedding for free](https://medium.com/@alnutile/local-llm-and-laravel-in-10-minutes-with-local-llm-embedding-for-free-ac96e49288d2)



## Make sure to setup Reverb 

Per the Laravel docs https://laravel.com/docs/11.x/reverb


## Modules
We are using this system https://github.com/nWidart/laravel-modules

Make sure to register your module in `bootstrap/providers.php`


## Ollama Notes

Make sure to run 
```bash
launchctl setenv OLLAMA_NUM_PARALLEL 3
```
and restart the service so you get multiple requests at a time
