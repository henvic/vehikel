Code-Coverage Analysis helps to let you know if your application tests are good enough.
PHPUnit's Code-Coverage Analysis: http://www.phpunit.de/manual/3.0/en/code-coverage-analysis.html

To make the coverage report you just have to run phpunit:

From the project's root do the following on the console:
```
$ mv tests/
$ phpunit
```

It will take a while processing all the code trough the static code analyzer.

The results are saved on this directory in the HTML format.
