# .git POST deploy

A set of small scripts to manage the automatic pulling from a hosted .git repo on commit.

## Summary

Hosted .git repository services like [github](http://github.com) and [Bitbucket](http://bitbucket.org) offer `POST` services that allow you run personally-hosted scripts after one of your hosted repositories is updated.  The file *deploy.php* (and the accompanying `DeployClass.php` file) should be uploaded to your personal server and set up to be called after your hosted repository is committed to.  The files in the `pre-deployment` directory are meant to be used just once, during the initial setup of the repo on your server – they are opinionated files and may not be suitable for everyone, but they work for us and make our lives a little easier...

## How to Use

Still need to set this part up with a better description...

### Deployment

Description here of how to use deploy.php and DeployClass.php.

### Initial Pre-Deployment setup

Description of the initial setup process and options here.

## WARNING!

These scripts were created and tested with our server setup in mind, and although we haven't experienced any issues there are some potentially destructive command-line executions that have the potential to really screw up your specific server setup.  Please use with caution, make backups, and remember that we make no gaurantees and cannot be held responsible for any issues that arise with your use of these scripts!

## LICENSE

(MIT License)

Copyright (C) 2013 [Oregano Creative](http://oreganocreative.com)

Copyright (C) 2013 [Tim Farley](http://timfarley.com)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.