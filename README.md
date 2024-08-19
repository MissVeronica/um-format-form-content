# UM Format Form Content shortcodes version 2.0.0
Extension to Ultimate Member for display of custom HTML format of User Profile form contents by a HTML formatted file being displayed by the shortcode and option to remove Profile Photos from selected Profile pages.

## Shortcodes
### <code>[format_form_content]</code>
Shortcode to be used in the "Profile View" form to display the custom HTML file. HTML file to be used is selected in a dropdown of the Plugin settings
### <code>[show_field meta_key=""]</code>
The plugin's shortcode for getting metadata from UM User cache for the current Profile owner fields incl formatting URLs
### <code>[um_user user_id="" meta_key=""]</code>
User Meta Shortcode from UM free extensions may be used for metadata display not available from the current User Profile cache where <code>[show_field meta_key=""]</code> is recommended to be used. 
https://ultimatemember.github.io/docs-v3/extended/article/1673-user-meta-shortcode.html

## UM Directory
1. Plugin will create a new web server directory <code>.../wp-content/uploads/ultimatemember/format_form_content</code> for saving the HTML formatting files for the Profile pages.

## UM Forms Builder
1. Create a new "Profile View" form
2. Add the shortcode <code>[format_form_content]</code> to a shortcode field of this form
3. No other Profile form fields are required by the plugin. 

## UM Settings -> Appearance -> Profile Menu
1. Enable Profile Menu Tabs only for the Profile owner ie no Tabs for visitors/viewers.

## UM Settings -> Extensions -> Format Form Content
Plugin version update check each 24 hours with documentation link.
1. * Select level of HTML tags allowed - Select one of the three levels of HTML tags allowed: Low, Medium, High. All three levels allow these additional tags &lt;ul&gt;, &lt;li&gt; and &lt;table&gt;, &lt;tr&gt;, &lt;th&gt;, &lt;td&gt; for this plugin.
2. * Select "Profile View" only Form - Select the Profile form with the shortcode field for the <code>[format_form_content]</code> shortcode.
3. * Select default User Profile Form - Select User Profile Form for the site\'s Members.
4. * Create a HTML file - Click to create a HTML file of this User Profile Form to "formatted-FORMID.html" in the upload directory. Rename file before editing.
5. * Select HTML format - Select HTML file format "List" or "Table" for the shortcode <code>[format_form_content]</code>
6. * Select HTML file for shortcode formatting - Select HTML file for use by the shortcode <code>[format_form_content]</code> in the formatting of the view only Profile Form
7. * Remove lines with empty field values - Click to remove empty lines (except the title) when the meta field value is empty.
8. * Profile Forms to remove Profile Photo - Select single or multiple Profile Forms for Profile Photo removal.

## UM Settings -> General -> Users
1. Profile Permalink Base - Current version of the plugin only supports "User ID" and "Username"

## Custom HTML formatted file
1. Create the custom HTML file of your Profile form with the plugin setting.
2. Rename and edit the file with an offline HTML Editor or a text Editor like removing unwanted fields or adding comments about fields for the viewer.
3. HTML allowed tags are the same as for an UM email template if the medium HTML tags selection is used. HTML List and Table tags are always available.
4. Use the Plugin shortcode to display current Profile fields <code>[show_field meta_key=""]</code> from the UM cache is recommended to use.
5. The UM Free Extension plugin "User Meta Shortcode" can be used for display of other Profile User's meta data values by reading the WP database.
6. UM email templates placeholders can also be used.
7. https://docs.ultimatemember.com/article/1340-placeholders-for-email-templates

#### Example of the "formatted.html" file
 <code>&lt;ul&gt;
    &lt;li&gt;Name: {display_name}&lt;/li&gt;
    &lt;li&gt;Profile description:&lt;div&gt;[show_field meta_key="description"]&lt;/div&gt;&lt;/li&gt;
    &lt;li&gt;My email is {email}&lt;/li&gt;
&lt;/ul&gt;</code>


#### Displayed format and plugin will do the placeholder translations and the shortcode before display at the Profile page:
<ul>
    <li>Name: {display_name}</li>
    <li>Profile description: <div>[show_field meta_key="description"]</div></li>
    <li>My email is {email}</li>
</ul>

## Translations & Text changes
1. Use the "Loco Translate" plugin.
2. https://wordpress.org/plugins/loco-translate/2.
3. For a few changes of text use the "Say What?" plugin with text domain: format-form-content
4. https://wordpress.org/plugins/say-what/

## HTML Guide
https://www.w3schools.com/html/default.asp

## User Profile Scrolling
1. Extension to Ultimate Member for User Profile Scrolling via ID, username, display name, first or last name, user email or random.
2. https://github.com/MissVeronica/um-user-profile-scrolling

## Updates
1. Version 1.1.0 Option to remove Profile Photos from selected Profile pages.
2. Version 1.2.0 Addition of a common shortcode for the Profile page which will activate the right UM Form depending on the current User viewing the Profile.
3. Version 1.2.1 Code improvement
4. Version 1.3.0 Removal of the Viewer Role selection. All User Roles can be equal. Profile Form selection depends on the URL User identification. Profile Menu Tabs must be updated with "Owner only". Limited support for "Profile Permalink Base" settings.
5. Version 1.4.0 Administrators are excluded from View only Profile Forms. Setting for creation of a HTML file of all Profile fields sorted by field title.
6. Version 1.4.1 Added missing "date" type in HTML file
7. Version 1.5.0 Addition of HTML file selection dropdown which replaces file name in the shortcode now only <code>[format_form_content]</code> is required
8. Version 2.0.0 UM extensions setting. Read updated readme file for changes. Replace the Profile page shortcode <code>[select_um_shortcode]</code> with UM default shortcode for the Profile Form.

## Installation & Updates
1. Download the plugin ZIP file at the green Code button
2. Upload and install with WP as a new WP Plugin, activate the plugin.
