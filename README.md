# Google Search Moodle Block #

This block plugin fetches the Google Custom Search Engine ID and Google API Key from the settings and displays the results of searching for "Moodle Block" within the block.

## How To Install Moodle with Postgres, Nginx and PHP on an Ubuntu 22.04 VPS


### Prerequisites
* Create a small droplet with Ubuntu 22.04
* Follow the [tutorial](https://www.digitalocean.com/community/tutorials/initial-server-setup-with-ubuntu-22-04) on setting up Ubuntu 16.04
* git should be installed by default

From here on we can just follow this tutorial. It assumes that you have a user with root privileges which means you need to type `sudo` before any other command.

## Step 1: Getting the packages
In order to run moodle some packages have to be installed first. Make sure to have up to date sources:
```command
sudo apt-get update
```

### Install Nginx:
Moodle works extremely well with Nginx as it offers a simple setup and serves static files blazingly fast. Even though moodle is a huge PHP application it has advanced caching features. So Nginx is our choice:

```command
sudo apt-get install nginx
```

There's also a [tutorial](https://www.digitalocean.com/community/tutorials/how-to-install-nginx-on-ubuntu-22-04) about Nginx with more details.
    
### Install postgresql:
Postgres is a very reliable database. Technically MariaDB or MySQL should do to but we're focusing on speed:

```command
sudo apt-get install postgresql postgresql-contrib
```

For additional guidance see the [tutorial](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-postgresql-on-ubuntu-22-04) on Postgresql. 

### Install PHP and its dependencies:
This rather long command will install all the necessary and recommended packages to run moodle on PHP 8.1:

```command
sudo apt-get install php-fpm php-curl php-gd php-xmlrpc php-intl php-xml php-zip php-mbstring php-soap php-pgsql
```

## Step 2: Installing moodle
Nginx has decided that `wwwroot` is under `/usr/share/nginx/html` which is fine for us. We will now install moodle there:

```command
cd /usr/share/nginx
```

The directory is not ready to be accessed by anybody but root, so we will enable our user to have access to it; moodle won't write into this directory, it will use the `moodledata` directory which we will setup at a later stage:

```command
sudo chown -R $USER:$USER html
```

Now enter the newly aquired directory and clone moodle:
    
```command
cd html
git clone https://github.com/moodle/moodle.git
```
    
This will clone the github moodle repository in a directory called `moodle`. Now you can do many things a lot easier. For example, if you moved from another hosting provider to DigitalOcean, you can just "check out" the version you last used and then check out the latest version. Or you can just stick with stable releases. There is a whole world of simple code management open for you. For this tutorial, we will simply check out the latest stable version which moodle always keeps in its own branch. To find out which version is latest for you, do this:

```command
cd moodle
git remote show origin
```
    
Currently the latest stable Version is `MOODLE_403_STABLE` so this is the one we're going to check out for this tutorial:

```command
git checkout MOODLE_403_STABLE
```
    
While Moodle also tags its releases, following a branch has the advantage that you can easily update following the weekly releases to the stable branches without following the current tags.

To update moodle a simple `git pull` will suffice to get the latest weekly release for the current branch. Once a new major release becomes available you will have to switch branches and set this branch to follow. So as soon as Moodle 4.4 becomes available you might want to run this command:

```command
git checkout MOODLE_404_Stable
```
    
### Adjustments
In your moodle directory there is a file called `config-dist.php`. Open and edit it:

```command
nano config-dist.php
```
    
There are a few values that need to be changed in order to work on your server (comments are stripped out for simplicity's sake):
```php
<?PHP
unset($CFG);
global $CFG;
$CFG = new stdClass();
$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = '<^>password<^>';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
    'dbpersist' => false,
    'dbsocket'  => false,
    'dbport'    => '',   
);
$CFG->wwwroot   = 'https://<^>example.com<^>';
$CFG->dataroot  = '/usr/local/moodledata';
$CFG->directorypermissions = 02777;
$CFG->admin = 'admin';
require_once(dirname(__FILE__) . '/lib/setup.php');
```

This is a complete config file. After you have changed the values, you save it as `config.php` to your moodle directory (make sure to use your own password and your own wwwroot). Also Note that `wwwroot` has been set to an `https` address. Unless you set up an SSL host file later on you should use `http`.

### Further Steps
The changes just performed set the pace for these next steps. You need to set up a moodle data directory and a cache directory:

```command
sudo mkdir /usr/local/moodledata
sudo mkdir /var/cache/moodle
```
    
Make them belong to the www-data user, which is in short Nginx:

```command
sudo chown www-data:www-data /usr/local/moodledata    
sudo chown www-data:www-data /var/cache/moodle
```
    
The first one is to store user uploads, session data and other things only moodle needs access to and which shouldn't be accessible from the web. The cache store helps to preserve files for faster caching.

## Step 3: Setting up the database
Now it's time to set up the database for moodle. To do so use the postgres user to create a new role called moodle which then will be able to handle the moodle database you are about to create:

```command
sudo su - postgres
psql
```
    
This will start a new postgres console:

```custom_prefix(postgres=#)
CREATE USER moodle WITH PASSWORD 'password';
CREATE DATABASE moodle;
GRANT ALL PRIVILEGES ON DATABASE moodle to moodle;
\q
```
    
With these commands, you've created a new database user called "moodle" that you granted all the necessary rights to administer the moodle database which you also created. To return to the shell use the `\q` command.

Exit user postgres and move to the next step:

```command
exit
```

## Step 4: Setting up Nginx
In a last step, tell Nginx how to serve your files. To do so create an Nginx host file:

```command
sudo nano /etc/nginx/sites-available/moodle
```

Add some basic config to the file so that you have unencrypted access to the site. You should then get a certificate for always encrypted traffic to your site:

```
server {
    server_name <^>example.com<^>;
    listen 80;
}
```
Save and link your new site to `sites-enabled` so that nginx picks up the config:

    ln -s /etc/nginx/sites-available/moodle /etc/nginx/sites-enabled/

Then restart your server via:

    sudo systemctl restart nginx
    
This will enable your moodle site on nginx.

You really should have SSL enabled as you and your users will send passwords to the server. How to get a free certificate from [Let's encrypt](https://letsencrypt.org) can be found in this fine [tutorial](https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-22-04). Once you have your certificate the Nginx host file should look like this:

```nginx
server {
    server_name <^>example.com<^>;
    root /usr/share/nginx/html/moodle;
    location / {
             root /usr/share/nginx/html/moodle;
             index index.php index.html index.htm;

             # moodle rewrite rules
             rewrite ^/(.*.php)(/)(.*)$ /$1?file=/$3 last;
          }

      # php parsing
      location ~ .php$ {
                         root /usr/share/nginx/html/moodle;
                         try_files $uri =404;
                         fastcgi_pass unix:/run/php/php8.1-fpm.sock;
                         fastcgi_index index.php;
                         fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                          include fastcgi_params;
                          fastcgi_buffer_size 128k;
                          fastcgi_buffers 256 4k;
                          fastcgi_busy_buffers_size 256k;
                          fastcgi_temp_file_write_size 256k;

               }

    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/<^>example.com<^>/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/<^>example.com<^>/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot
}
server {
    if ($host = <^>example.com<^>) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


    server_name <^>example.com<^>;
    listen 80;
    return 404; # managed by Certbot


}
```

Note, some of these settings are added to the config via let's encrypt. Some are your settings. Compare the output you get from let's encrypt.

Make sure to replace the `<^>example.com<^>` entries with your domain. Also note that this will make your moodle site only accessible through https. If you want redirects please read the aforementioned tutorial.

Just in case you don't want to enable SSL, either because you don't have a domain yet or you just want to see moodle in action without any user you can also use this host file. Just make sure to use only one or the other as redirects will not happen.

```nginx
server {
    server_name          <^>example.com<^>;
    listen 80;
        root /usr/share/nginx/html/moodle;
        rewrite ^/(.*\.php)(/)(.*)$ /$1?file=/$3 last;

        location ^~ / {
                try_files $uri $uri/ /index.php?q=$request_uri;
                index index.php index.html index.htm;

                location ~ \.php$ {
                        include snippets/fastcgi-php.conf;
                        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
                }
        }
}    
```

Now enable your moodle site:

```command
sudo ln -s /etc/nginx/sites-available/moodle /etc/nginx/sites-enabled/moodle
```
    
And restart up your nginx server:

```command
sudo service nginx restart
```
    
After this last step you can test your new moodle platform. Point your browser to your domain or your server's IP address.

Moodle will ask you a few questions while it installs.

## Installing via git ##

After navigating to the 'blocks' directory within the Moodle installation directory, clone the repository for 'google_static_search'."

```command
cd {your/moodle/dirroot}/blocks
git clone https://github.com/SangyulCha/google_static_search.git
```

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Setting up After Installation ##

Navigate to Site administration -> Plugins -> Manage Blocks. Locate the 'Google Search Moodle Block' and access its settings. Set up the Google Custom Search Engine ID and Google API key.

## License ##

2024 sangyul cha <eddie6798@gmail.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
