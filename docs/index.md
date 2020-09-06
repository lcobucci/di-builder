# Overview

`lcobucci/di-builder` is a library that provides a powerful way to create compiled DI containers.
We rely on the [Symfony DI component] to perform such task. 

## Motivation

The underlying component is just brilliant.
However, it doesn't provide a self-contained solution to be used **outside the Symfony ecosystem**.
The logic you see in this library is essentially present in the Symfony HTTP Kernel (or micro kernel).

The goal here is to reliably produce compiled DI containers that don't harm developer experience.

## Support

If you're having any issue to use the library, please [create a GH issue].

## License

The project is licensed under the BSD-3-Clause license, see [LICENSE file].

[Symfony DI component]: http://symfony.com/doc/current/components/dependency_injection/introduction.html
[create a GH issue]: https://github.com/lcobucci/di-builder/issues/new
[LICENSE file]: https://github.com/lcobucci/di-builder/blob/master/LICENSE
