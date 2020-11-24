# ancarda/psr7-string-stream

_Minimal string based PSR-7 StreamInterface implementation_

[![License](https://img.shields.io/badge/license-MIT-teal)](https://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://poser.pugx.org/ancarda/psr7-string-stream/v/stable)](https://packagist.org/packages/ancarda/psr7-string-stream)
[![Total Downloads](https://poser.pugx.org/ancarda/psr7-string-stream/downloads)](https://packagist.org/packages/ancarda/psr7-string-stream)

PSR-7 String Stream was born out of frustration working with PSR-7's
StreamInterface. Most implementations typically use PHP Streams, which aren't
the best to work with. I've run into bugs where harmless operations on
Requests, such as `withHeaders` causes the underlying Body's destructor to be
called, which closes the underlying stream. Since resources can't be cloned,
this can cause the body to be destroyed.

This package, as the name implies, implements StreamInterface using strings
which will survive clone+destroy.

If you're using this in production,

	composer require ancarda/psr7-string-stream

If you're just using this in functional or unit tests, it can go in your
`require-dev` section:

	composer require --dev ancarda/psr7-string-stream

## Useful Links

* Source Code:   <https://git.sr.ht/~ancarda/psr7-string-stream/>
* Issue Tracker: <https://todo.sr.ht/~ancarda/psr7-string-stream/>
* Mailing List:  <https://lists.sr.ht/~ancarda/psr7-string-stream/>
