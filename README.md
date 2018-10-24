<h1>Staks - Personal Precious Metals Management Interface</h1>

This is a personal precious metals manager. 

I'm a big fan of <a href="https://reddit.com/r/silverbugs">SilverBugs on Reddit</a> and I started tracking all my purchases in an excel sheet which quickly got out of hand. So, I had some developers create me a simple interface. Which, is what you get with Staks. 

You can add a variety of items in your 'stack' and mark them as sold, or lost, or even stolen. You can view graphs of your overall profit, loss, inclusive of shipping/freight charges, numismatic value and spot value. 

You can edit the vendors, mints, and shapes of the items in your 'stack' as well. 

You can also use this in 'offline' mode in that you create your data, download a csv, and then delete it. Useful if you're paranoid and want to store all your data locally but use this for seeing graphs or modifying your stack easily. 

Currently supports Gold, Platinum, Silver, Paladium with automatic updates on value. The app grabs daily metal prices via a cron script. 


<H2>LICENSE</h2>
GPL

<H2>INCLUDED SOFTWARE</H2>
<ul>
  <li><a href="https://www.yiiframework.com/">YII Framework</a></li>
<li><a href="https://themeforest.net/item/porto-admin-responsive-html5-template/8539472">Porto Admin Them</a></li>
</ul>

<H2>INSTALLATION INSTRUCTIONS</H2>

1. Create your hosting environment
2. As the user the server or virtualhost is running as, enter the web host root directory
<pre>$ cd /home/user/public_html/</pre>
3. Issue the following commands to get composer up
<pre>$ php composer.phar self-update
$ php composer.phar install
$ php composer.phar global require "fxp/composer-asset-plugin:1.0.0-beta3"</pre>

4. Pull down the source of this repository 

<pre>$ git clone git@bitbucket.org:flewid/staks.git .</pre>

5. Import the mysql database in your regular fashion. 
6. Edit the mysql database information in the config file 
<pre>$ cp ./config/db_prod.php ./config/db.php && nano ./config/db.php</pre>

7. Visit http://yourdomain.com and login with the default info: 

<p><strong>username:</strong> admin@domain.com<br />
   <strong>password:</strong> admin123</p>

8. Change the password for the user, and create more if needed. 

9. Test it out and report any bugs!

<h2>DEFAULT LOGINS</h2>
<p><strong>username:</strong> admin@domain.com<br />
   <strong>password:</strong> admin123</p>

<h2>EXAMPLE DATABASE DOWNLOAD</h2>
<p>You can grab the <a href="https://staksdemo.info/sql/staks.sql">SQL file from this link</a>.</p>

<h2>EXAMPLE CRON COMMAND</h2>
<pre>
# for staks
0 22 * * * wget -O- https://staksdemo.info/history/cron3
</pre>

<h2>DEMO SITE</h2>
<P>View the <a href="https://staksdemo.info">demo site here</a>, using the same credentials as above</p>



<H2>SCREENSHOTS</H2>

<h3>Splash Screen</h3>
<img src="https://staksdemo.info/web/screenshots/StakrLoginScreen.png" />
  
<h3>Login Screen</h3>
<img src="https://staksdemo.info/web/screenshots/StaksLoginScreen-Login.png" />
 
<h3>Main Dashboard Screen</h3>
<img src="https://staksdemo.info/screenshots/staks_dash.png" />
 
<h3>My Stack Editing Screen</h3>
<img src="https://staksdemo.info/screenshots/StakrMyStack.png" />

<h3>Settings - Grades Screen</h3>
<img src="https://staksdemo.info/screenshots/stakrsettings_grades.png" />

<h3>Settings - Types</h3>
<img src="https://staksdemo.info/screenshots/stakrsettingstypes.png" />

<h3>Settings - Mints</h3>
<img src="https://staksdemo.info/screenshots/stakr_settings_mints.png" />

<h3>Settings - Vendors</h3>
<img src="https://staksdemo.info/screenshots/stakr_settings_vendors.png" />

<h3>Settings - Shapes</h3>
<img src="https://staksdemo.info/screenshots/staks_settings_shapes.png" />

<h3>Spot Price History</h3>
<img src="https://staksdemo.info/screenshots/stakr_spot_price_history.png" />

<h3>Activity Log</h3>
<img src="https://staksdemo.info/screenshots/stakr_activity_log.png" />     
