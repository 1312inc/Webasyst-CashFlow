# Webasyst Cash Flow #

Webasyst Cash Flow is a simple account & cash flow forecasting app for teams and small business.
Self-hosted. REST API + Vue.js SPA web client.

https://www.webasyst.com/store/app/cash/

## Server Requirements ##

	* Web Server
		* e.g. Apache or IIS

	* PHP 7.4+
		* spl extension
		* mbstring
		* iconv
		* json
		* gd or ImageMagick extension

	* MySQL/MariaDB

## Installing Webasyst Framework (required first!) ##

Install Webasyst Framework via https://github.com/webasyst/webasyst-framework/ or https://developers.webasyst.com/

## Installing the Cash Flow app (once Webasyst is installed) ##

1. Once Webasyst is installed, get the Cash Flow app code into your /PATH_TO_WEBASYST/**wa-apps/cash/** folder:

		cd /PATH_TO_WEBASYST/wa-apps/
		mkdir cash
		git clone git://github.com/1312inc/Webasyst-CashFlow.git ./

2. Add the following line into the `wa-config/apps.php` file (this file lists all installed apps):

		'cash' => true,

3. Done! Run Webasyst backend in a web browser and click on the Cash Flow app icon in the main app list. In the app icon is missing, make sure to clear Webasyst cache (flush `wa-cache/` folder contents).
