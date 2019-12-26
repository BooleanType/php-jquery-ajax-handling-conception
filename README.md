# php-jquery-ajax-handling-conception

#### This functionality is advice on making requests to the server through the AJAX. This is just a *template* that can be copied from project to project and which allows you to process all possible server responses, adhering to the same style throughout the project.

- The technology bundle used is PHP and JavaScript, but since this is only a concept, a similar approach can be used in other programming languages.

### Server side
#### Requirements
1. Encapsulate all code in `try… catch` statement.
2. Every condition, which suggests a failure, should throw PHP Exception, for example:

```php
if ($reg_id == '') {
	throw new Exception(json_encode([500, 'Error description.']));
}
if(!$someThing->save()) {
	throw new Exception('Something is not saved');
}
```
#### Example of pseudo-code processing an AJAX request on the server
```php
try {
	if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        throw new Exception(json_encode([403, 'Forbidden']));
    }
    
	if (array_key_exists('id', $_POST)) {
		$id = (int) $_POST['id'];
		$news = News::findOne($id);

		if (!is_object($news)) {
			throw new Exception(json_encode([404, 'No news']);
		}
		// Update found record.
		$news->author = 'Stephen King';
		if (!$news->save()) {
			throw new Exception(json_encode([404, 'Updating error']);
		}
		// Success (return status and encoded data).
		die(json_encode([200, json_encode($news)]));

	} else { // Wrong data from user.
		throw new Exception(json_encode([400, 'Bad request']));
	}
} catch (Exception $e) {
	die($e->getMessage());
}
```
Exception argument can be JSON-parsable string, which **must** contain status code, (**Case 1**) or just
error message (**Case 2**).

##### Case 1

```php
throw new Exception(json_encode([404, 'No news']);
 ```

- `404` is a status code for appropriate handling on client side. Something like HTTP status codes
(https://en.wikipedia.org/wiki/List_of_HTTP_status_codes). You can choose any code you want;
- `'No news'` is an example error message, which will be rendered for end user.
    
##### Case 2 (without status code):

```php
throw new Exception('Some error description');
 ```
 
In this case data parsing will fail on client side, so `catch` block in `done()` method will triggered. `msgText` variable will contain 'Some error description' string (see [Client Side](#client-side) section).
 
> **WARNING**: it is necessary to understand, if all operations really should throw Exceptions. Exception throwing stops code execution in `try {}` block, and further control passes to `catch()` block. That's why such operations as logging shouldn't stop all process, if writing to log table is failed.

#### Success answer format

```php
die(
	json_encode([           // *3*
		200,                // *1*
		json_encode($news)  // *2*
	])
);
 ```

- \*1\* is **successful** status code, **required**;
- \*2\* is **optional**. Data, returned by AJAX. **If data is array, it should be encoded by `json_encode()`**;
- \*1\* and \*2\* arguments should be elements of encoded array (\*3\*), which is passed to `die()`.

##### Examples:
```php
// Only status, without data.
die(json_encode([200]));
// Successful status and $news array.
die(json_encode([200, json_encode($news)]));
// Successful status and appropriate message.
die(json_encode([200, 'Articles were updated successfully!']));
 ```
 
----
### Client side

#### Example of handling AJAX request on client side
Code below is a template of how AJAX requests handling on the client side can be organized.

```javascript
let msgText;
$.ajax({
	'type': 'POST',
	'url': url,
	'data': { ... }
	
}).done(function (res) {
	let data, statusCode;
	try {
		data = JSON.parse(res);
		if ($.isPlainObject(data) || $.isArray(data)) { // Should be object (or arr).
			statusCode = data[0];
			if (statusCode == 200) { // If success.

				// DO YOUR SUCCESS LOGIC HERE!!!

			} else { // Status code != 200.
				throw new TypeError(data[1]);
			}

		// Data is parsable, but it is not an obj/arr, as planned (for ex., '"foo"', 'true', 'null'). 
		} else {
			throw new TypeError(data);
		}
	} catch (e) {
		// SyntaxError exc throws, if res is unparsable (JSON.parse() failed):
		// unhandled exception is thrown, which isn't related to validation (for ex., UnknownPropertyException).
		msgText = (e.name == 'SyntaxError') ? res : e.message;

		// DO ERROR LOGIC HERE (IF NEEDED).

	}
}).fail(function (jqXHR, textStatus, errorThrown) {    
	msgText = errorThrown;

	// DO ERROR LOGIC OR COPY IT FROM PREVIOUS CATCH STATEMENT (IF NEEDED).
    
}).always(function () {
	// Always triggers - in done() and fail() case as well.

	// DO LOGIC, WHICH SHOULD BE PRESENT ANYWAY - IF AJAX IS SUCCESSFUL OR FAILED (IF NEEDED).

});
 ```

There is AJAX handling template in example above, which takes into account all possible errors
that have occurred on the server.

In code...
```javascript
if (statusCode == 200) { // If success.
	// DO YOUR SUCCESS LOGIC HERE!!!
```
... you can add *additional logic*, related to successful query, for example:
```javascript
if (statusCode == 200) { // If success.
	msgText = 'Saving was successful!';
	// Work with data, retrieved from server in success case.
	let requestedData = JSON.parse(data[1]);
```
You can handle any status code, which was returned from server, for example:
```javascript
// If server returns a lot of statuses, you can use switch() statement.
if (statusCode == 200) { // If success.
	msgText = 'Saving was successful!';
	msgContainerBgColor = 'green';
	
} else if (statusCode == 404) { // Not an error, just information.
	msgText = 'No news';
	msgContainerBgColor = 'blue';
	
} else { // Status code = 500 or another.
	throw new TypeError(data[1]);
	// Don't do error logic here
	// (defining msgText and msgContainerBgColor variables, etc.);
	// do it in catch() block instead (like in full example above).
}
```
`always()` method of the jqXHR object allows to implement logic, which always should be present.

#### Demo
Working demo can be found in `demo` directory of current project. Code in that project is just an example, so, you can modify it according to your needs.
Entrance file is `view.html`.