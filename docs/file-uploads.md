# File Uploads

## UploadedFile Object

When you retrieve a FileType's data from a form, an instance of [UploadedFile](/src/UploadedFile.php) will be returned.
This is a small wrapper object around PHP's native uploaded file array.

## File Constraints

The following constraints can be used on the FileType field:

| Constraint                                      | Description                                       |
|-------------------------------------------------|---------------------------------------------------|
| [Extension](/src/Constraint/File/Extension.php) | Ensures the file has an allowed extension         |
| [MimeType](/src/Constraint/File/MimeType.php)   | Ensures the file has an allowed mime type         |
| [Size](/src/Constraint/File/MimeType.php)       | Ensures the file size is between an allowed range |

See the [file upload example](/examples/fileupload/index.php) for usage examples of these constraints.

[Return to index](index.md)
