=== Mailing ===
Contributors: parczynski
Tags: email, newsletter, marketing, mailing
Requires at least: 5.2
Tested up to: 6.0
Requires PHP: 7.4
Stable tag: 0.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin for managing email subscriptions and sending newsletters.

== Installation ==

### INSTALL FROM WORDPRESS PLUGINS STORE

1. Go to page *Plugins* -> *Add New* in your dashboard, search for *\"Mailing\"* and click *\"Install now\"*

2. Go to the page *Plugins* -> *Installed Plugins* in your dashboard, find there Mailing plugin and click *Activate* near to plugin\'s name.

3. Configure plugin on the page *Mailing* -> *Mailing Options*

### INSTALL MANUALLY

1. Download the zip archive with plugin\'s files from https://wordpress.org/plugins/mailing and upload content of the archive to your plugins directory, which is by default */wp-content/plugins/*.

2. Go to the page *Plugins* -> *Installed Plugins* in your dashboard, find there Mailing plugin and click *Activate* near to plugin\'s name.

3. Configure plugin on the page *Mailing* -> *Mailing Options*


### CREATING SUBSCRIPTION GROUPS

In case you need to separate your subscribers to different groups, you should create those groups on the page *Mailing* -> *Subscription Groups*. When you will create newsletters you will be able to target your audience by those groups. If you don't need to separate subscribers, you could just skip it and add subscription forms to your pages.


### CONFIGURING SUBSCRIPTION FORMS

Thanks to WordPress Block Editor, creating subscription forms is simple and does not require any coding. To add a form to a page just start creating a WordPress page like you normally would. Simply click *Pages* -> *Add new*. This will launch the Block Editor.

To add a form click *Add block* button (button with + sign in it). This will toggle the block inserter. In the block inserter find *Subscribe Form* under the section *Widgets* and click it. Signup form will appear in the editor. You can customize this form using visual editor and *Form Settings* block which will appear in the right column of your screen.

That's it. After you publish this page your visitors will be able to subscribe to your newsletters.


### CREATING NEWSLETTERS

Sending newsletters to your subscribers is even easier than configuring subscription forms. Just go to *Mailing* -> *Newsletters* in your dashboard and click *Add new* button. 

Write content of the email using Block Editor, specify email subject as a newsletter title, if you need to send a newsletter to a certain group of subscribers, choose this group in block *Targeting* in the right column of your screen. 

When your newsletter will be ready just click *Send* button in the top right corner of the screen.