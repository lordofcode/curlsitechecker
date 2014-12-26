curlsitechecker
===============

Scrape title and image of websites

Author: Dirk Hornstra

Date:   2014-12-26

Place the folder curlsitechecker in the plugins-folder of WordPress (wp-content/plugins).

Activate the module in wp-admin.

In the module (Scraper - Configuratie) set a value for CRONKEY. With it you can call the CRON-script to fetch the data.

Configuring the sites to scrape:

By clicking on the # below "bewerken" you get a pop-up where you can fill in the XPATH-value for retrieving the data (e.g. //article/header/h2/a).

Executing the cron-jobs goes by:

http(s)://[YOUR DOMAIN]/wp-content/plugins/curlsitechecker/cron.php?cronkey=[YOUR CRONKEY VALUE]

It fetches the data once a day. If you want to "force" the job to execute a second, third, etc. time, you have to use:

http(s)://[YOUR DOMAIN]/wp-content/plugins/curlsitechecker/cron.php?cronkey=[YOUR CRONKEY VALUE]&force=true
