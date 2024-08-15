# UM Format Form Content shortcode
Extension to Ultimate Member for display of custom HTML format of User Profile form contents by a HTML formatted file being displayed by the shortcode and option to remove Profile Photos from selected Profile pages.

## UM Forms Builder
1. Create a new "Profile View" form
2. Later you will add a shortcode field to this form for the display of HTML customized Profile info

## Profile page
1. Add the shortcode and replace your current UM shortcode(s) with [select_um_shortcode] managed by this plugin for the display of Profile form depending on current User's Role.

## UM Settings -> Appearance -> Profile Menu
1. Enable Profile Menu Tabs only for User Profile Roles ie no Tabs for the Viewer Role. This will remove the UM meny line 

## UM Settings -> General -> Users
1. * Select level of HTML tags allowed - Select one of the three levels of HTML tags allowed: Low, Medium, High
2. * Select the User Role for the viewer - Select the Profile Role which will see the Profiles custom formatted User info.
3. * Select Profile view only Form - Select the Profile form with the shortcode field for the [format_form_content] shortcode.
4. * Select default User Profile Form - Select User Profile Form for the site\'s Members.
5. * Profile Forms to remove Profile Photo - Select single or multiple Profile Forms for Profile Photo removal.

## Custom HTML formatted file
1. Create the custom HTML file "formatted.html" with an offline HTML Editor or a text Editor.
2. HTML allowed tags are the same as for an UM email template if the medium HTML tags selection is used.
3. The UM Free Extension plugin "User Meta Shortcode" can be used for display of Profile User meta values.
4. https://ultimatemember.github.io/docs-v3/extended/article/1673-user-meta-shortcode.html
5. UM email templates placeholders can also be used.
6. https://docs.ultimatemember.com/article/1340-placeholders-for-email-templates
7. Create a new web server directory <code>.../wp-content/uploads/ultimatemember/format_form_content</code> with cPanel FileManager or your FTP Client
8. Upload the HTML file "formatted.html" to the "format_form_content" directory with your FTP client
9. Add the shortcode <code>[format_form_content]formatted.html[/format_form_content]</code> to a shortcode field of your "Profile View" form in UM Forms Builder.

#### Example of the "formatted.html" file
 <code>&lt;ul&gt;
    &lt;li&gt;Name: {display_name}&lt;/li&gt;
    &lt;li&gt;Profile description:&lt;div&gt;[um_user meta_key="description"]&lt;/div&gt;&lt;/li&gt;
    &lt;li&gt;My email is {email}&lt;/li&gt;
&lt;/ul&gt;</code>


#### Displayed format and plugin will do the placeholder translations and the shortcode before display at the Profile page:
<ul>
    <li>Name: {display_name}</li>
    <li>Profile description: <div>[um_user meta_key="description"]</div></li>
    <li>My email is {email}</li>
</ul>

## Translations & Text changes
1. For a few changes of text use the "Say What?" plugin with text domain ultimate-member
2. https://wordpress.org/plugins/say-what/

## HTML Guide
https://www.w3schools.com/html/default.asp

## User Profile Scrolling
1. Extension to Ultimate Member for User Profile Scrolling via ID, username, display name, first or last name, user email or random.
2. https://github.com/MissVeronica/um-user-profile-scrolling

## Updates
1. Version 1.1.0 Option to remove Profile Photos from selected Profile pages.
2. Version 1.2.0 Addition of a common shortcode for the Profile page which will activate the right UM Form depending on the current User viewing the Profile.
3. Version 1.2.1 Code improvement

## Installation & Updates
1. Download the plugin ZIP file at the green Code button
2. Upload and install with WP as a new WP Plugin, activate the plugin.
