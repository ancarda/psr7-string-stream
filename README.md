# ancarda/psr7-string-stream

_Minimal string based PSR-7 StreamInterface implementation_

[![Latest Stable Version](https://poser.pugx.org/ancarda/psr7-string-stream/v/stable)](https://packagist.org/packages/ancarda/psr7-string-stream)
[![Total Downloads](https://poser.pugx.org/ancarda/psr7-string-stream/downloads)](https://packagist.org/packages/ancarda/psr7-string-stream)
[![License](https://poser.pugx.org/ancarda/psr7-string-stream/license)](https://choosealicense.com/licenses/mit/)
[![Build Status](https://travis-ci.org/ancarda/psr7-string-stream.svg?branch=master)](https://travis-ci.org/ancarda/psr7-string-stream)
[![Coverage Status](https://coveralls.io/repos/github/ancarda/psr7-string-stream/badge.svg?branch=master)](https://coveralls.io/github/ancarda/psr7-string-stream?branch=master)

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
