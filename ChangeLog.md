2016.12 - version 0.12.0

ALL
* Added support for SAS authentication.
* Merged `StorageAuthScheme` into `SharedKeyAuthScheme` and `TableSharedKeyLiteAuthScheme` now inherits `SharedKeyAuthScheme`. This is because Azure Storage now supports Shared Key authentication and SAS authentication so the name `StorageAuthScheme` was not representative anymore.

2016.11 - version 0.11.0

ALL
* Fix error string when an error occurs while parsing a connection string and is passed to _createException in `MicrosoftAzure\Storage\Common\Internal\ConnectionStringParser`.
* Added support to create Guzzle's customizable retry middleware to handle the request after the response is received. Also added a default retry policy in case a retry policy is not specified.
* Fixed a bug in unit test where getting properties from service failed to match the expected result due to previous settings have not yet taken effect.
* Fixed some coding style issue. This work will be continued in the following serveral releases, and strictly follows PSR-2 coding style.
* Updated the documentation of `setMetadata`, now in the comments of the following methods `$metadata` is an array instead of a string.
```
MicrosoftAzure\Storage\Blob\Models\Blob.setMetadata
MicrosoftAzure\Storage\Blob\Models\CommitBlobBlocksOptions.setMetadata
MicrosoftAzure\Storage\Blob\Models\GetContainerPropertiesResult.setMetadata
MicrosoftAzure\Storage\Blob\Models\GetBlobResult.setMetadata
MicrosoftAzure\Storage\Blob\Models\GetBlobPropertiesResult.setMetadata
MicrosoftAzure\Storage\Blob\Models\GetBlobMetadataResult.setMetadata
MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions.setMetadata
MicrosoftAzure\Storage\Blob\Models\CreateBlobSnapshotOptions.setMetadata
MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions.setMetadata
MicrosoftAzure\Storage\Blob\Models\CopyBlobOptions.setMetadata
MicrosoftAzure\Storage\Blob\Models\Container.setMetadata
MicrosoftAzure\Storage\Queue\Models\CreateQueueOptions.setMetadata
MicrosoftAzure\Storage\Queue\Models\GetQueueMetadataResult.setMetadata
MicrosoftAzure\Storage\Queue\Models\Queue.setMetadata
```
* Removed test code from composer package.
* `StorageAuthScheme::computeCanonicalizedResource` assumes that the query parameters are already grouped. That is, multi-value query parameters must be assembled using `ServiceRestProxy::groupQueryValues`. This fixes an issue with other single-value query parameters that might contain the separator character in the value.

Blob
* Added support for user to upload large files with minimum memory usage.
* Added concurrent upload for Block Blob.
* Added `MicrosoftAzure\Storage\Blob.saveBlobToFile` for user to download a blob into a file.

2016.08 - version 0.10.2

ALL
* Allow passing an array of options to a service. Currently only Guzzle options are supported via the `http` parameter.

2016.05 - version 0.10.1

Blob
* Fixed the issue that blobs upload with size multiple of 4194304 bytes and larger than 33554432 bytes.
* Fixed the issue that extra / is appended in blob URL.

2016.04 - version 0.10.0

ALL
* Separated Azure Storage APIs in Azure-SDK-for-PHP to establish an independent release cycle.
* Remove all pear dependencies: HTTP_Request2, Mail_mime, and Mail_mimeDecode. Use Guzzle as underlying http client library.
* Update storage REST API version to 2015-04-05.
* Change root namespace from "WindowsAzure" to "MicrosoftAzure/Storage".
* When set metadata operations contains invalid characters, it throws a ServiceException with 400 bad request error instead of Http_Request2_LogicException.

Blob
* Fixed the issue that upload large block blob fails. (https://github.com/Azure/azure-sdk-for-php/pull/757)
* MicrosoftAzure\Storage\Blob\Models\Blocks.setBlockId now requires a base64 encoded string.

Table
* MicrosoftAzure\Storage\Table\Models\Property.getEdmType now returns EdmType::STRING instead of null if the property data type is not set in server.