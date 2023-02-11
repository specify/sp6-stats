# Specify 6 Usage Statistics

This website provides some information on the usage
of [Specify 6](https://github.com/specify/specify6).

## Requirements

## `Private`

The UI for displaying the usage stats.

### Installation

### Configuration

1. Clone this repository
2. Install Docker and Docker compose
3. Copy `./env.example` file to `./.env` and change the variables as applicable
4. Edit `docker-compose.yml` in all the places where you see `CHANGE THIS:`
5. Copy `./sp7-stats/config/auth.example.conf` to
   `./sp7-stats/config/auth.conf` and change the variables as applicable. That
   would require you to create a GitHub OAuth
   App. [See the instructions](https://github.com/specify/nginx-with-github-auth#installation)
6. Generate `fullchain.pem` and `privkey.pem` (certificate
   and the private key) using Let's Encrypt and put (or symlink) these files
   into the `./sp7-stats/config/` directory.

   While in development, you can generate self-signed certificates:

   ```sh
   openssl req \
      -x509 -sha256 -nodes -newkey rsa:2048 -days 365 \
      -keyout ./sp7-stats/config/privkey.pem \
      -out ./sp7-stats/config/fullchain.pem
   ```

7. It is recommended to set up daily cron to the following
   location `http://<yourdomain>/cron/refresh_data.php`. This will automatically
   unzip the files and compile the information so that users can get up to date
   data.
8. It is also highly recommended to create indexes for `track` and `trackitem`
   tables in the `stats` database:
   ```sql
   SHOW INDEX FROM `track`; # Show existing indexes for `track`
   SHOW INDEX FROM `trackitem`; # Show existing indexes for `trackitem`
   CREATE UNIQUE INDEX `track_index` on `track` (`trackid`,`ip`,`timestampcreated`); # Create indexes for `track`
   CREATE UNIQUE INDEX `trackitem_index` on `trackitem` (`trackid`,`name`,`value`,`countamt`); # Create indexes for `trackitem`
   ```
   
### Start up

#### Production

Start the containers: `docker compose up -d`

#### Development

Start the containers:

```bash
docker-compose \                                                                       2m 53s
  -f docker-compose.yml \
  -f docker-compose.development.yml \
  up --build
```

In development, MariaDB database is accessible outside Docker and a
`./source-data/` directory is mounted into the container.

#### Optional settings

You can go over the other settings in the `./config/optional.php`
and `./config/cache.php` files and see if there is anything you would like to
adjust.

For example, you can change the duration of time before compiled data is
considered out of date by changing `CACHE_DURATION`.
The default value is set to 7
days[![analytics](http://www.google-analytics.com/collect?v=1&t=pageview&dl=https%3A%2F%2Fgithub.com%2Fspecify%2Fsp6-prod&uid=readme&tid=UA-169822764-7)]()

## `Public`

The endpoint for collecting incoming stats from Specify 6

The `public` directory is meant to be served at `specify6-prod.nhm.ku.edu`.

## Credit for used resources

There were snippets of code/files from the following resources used:

- [Bootstrap 4.5.0](https://github.com/twbs/bootstrap)
- [jQuery 3.5.1](https://github.com/jquery/jquery)
- [Chart.js](https://github.com/chartjs/Chart.js)
- [Specify 6 icon](https://www.specifysoftware.org/wp-content/uploads/2017/06/sp_project_square-1-150x150.png)
- [Cache_query.php](https://gist.github.com/maxxxxxdlp/91a39c6864365d7a8e813e19b819bb0d)
- [unix_time_to_human_time.php](https://gist.github.com/maxxxxxdlp/54b7d6648a60a21a635f902de7a5d6b4)
