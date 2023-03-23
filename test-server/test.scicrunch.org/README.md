# SciCrunch UI Code

The SciCrunch UI is done in PHP using the Unify Templating as the guide for designing the site. 

## Requirements
* PHP 5.2+ 
* MySQL 

## Folder Descriptions
* assets: The unify assets (JS, CSS for components)
* browsing: All files pertaining to SciCrunch Browse Tab
* classes: contains all the PHP classes
* communities: PHP code pertaining to Communities inside SciCrunch (homepage, search views)
* components: the PHP/HTML/CSS for community/SciCrunch components
* create-pages: the files pertaining to SciCrunch Create Tab
* css: SciCrunch/Community specific CSS Files
* faqs: The SciCrunch faqs pages
* forms: the PHP files that transforms the user input in HTML forms to the DB
* images: the SciCrunch specific images
* js: the SciCrunch/Community specific Javascript files
* php: Typically these are scripts called in the interface dynamically to fill dialog boxes
* profile: all files pertaining to the My Account Tab
* ssi: the server side includes for SciCrunch, namely the site wide header and footer
* upload: the folder to hold user uploaded files for communities and components
* validation: validation code to check form inputs
* vars: any created files that would be too expensive to query dynamically

## Commonly used variables
* uid = User ID: used a lot in classes
* cid = Community ID: used a lot in classes and functions
* component = Component ID: used in Component Data and tags to refer to the component it is part of
* nif = The View ID of a source (also source): used in Search Class and in communities

## Common Class Functions
* create(vars)
  * Description: The constructor of the class for brand new objects (used from input forms)
  * vars: a key:value array of column:value pairs for the object
* createFromRow(vars)
  * Description: The constructor of the class for objects gotten from the DB
  * vars: a key:value array of column:value pairs from the DB table
* insertDB()
  * Description: Used to handle the specific insertion of a brand new object into the DB
* updateDB()
  * Description: Used to handle the updating of this object in a DB
* getByID(id)
  * Description: Used to get a single record from the DB by a given ID
  * id: the unique ID for an object in the DB

## Interface DB Model
  The interface composed of HTML, CSS, and Javascript. These are client*side code in that the server passes the code
  to a User's browser to interpret (hence differences in how things look across browsers). Client side code can be
  viewed and altered by the user, while server side code is never shown to the user. PHP is server side code in that
  the server runs the PHP code on the server and the result of the run should be HTML or a response telling the server
  where to go instead. You can use PHP code and HTML code within each other, but PHP code will be executed before the
  page is shown to the user and will not run after the page loads. JS and CSS handle the post*page load interactions
  like opening and closing dialogs.

  Forms are created by PHP/HTML and filled out by users. Forms have a "method" and an "action". The method is usually
  either post or get. Get will show the user entered data as a parameter in the URL while the POST will pass it as data
  silently. GET is used more for search forms while POST is used for user data. The action determines where the browser
  will go after submission. Most SciCrunch data forms will go to the /forms folder, where those scripts will handle the
  user data and push that data into the Database/perform actions and then tell the server to go somewhere to show the
  result.

  The /form scripts access a $_POST global variable that holds post data in a key:value array. The script will pull the
  data from that variable and pass that into the appropriate Class for what is being submitted and calls the classes
  Class::insertDB or Class::updateDB depending on what the action is. The class functions use the Connection class to
  talk to the Database if needed.

## NIF Data Sources
  SciCrunch interfaces with the NIF data services through API enpoints documented here: http://nif-services.neuinfo.org/servicesv1/index.html
