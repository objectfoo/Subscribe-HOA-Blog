# Plugin features

* <del>admin section</del>
    * <del>API key ( global )</del>
    * <del>List Name</del>
    * <del>UUID Prefix</del>
    * <del>Send Announcements</del>
* <del>add a shortcode to print a sub/unsub form for dreamhost announce-list</del>
* respond to feedback queries by printing virtual pages for
    * subscribed: confirmation email has been sent
    * already_subscribed: goodbye page
    * already_on: you are already on the list
    * not_subscribed: you are not on the list
    * invalid_email: malformed email address
    * welcome: Email confirm (they just clicked the confirm link in their email) **might be wrong**
* <del>send message to dreamhost announce-list server when a post is published for the first time</del>

**todo**

Abort previous nix the shortcode plan, new plan: do it all in a shortcode, nix Feedbackpages / wp_redirect stuff.

Next up: finish `ShortCode_SHB::replace_shortcode` method, it needs render methods for all the different response types.