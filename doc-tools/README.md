# Generating documentation from sources

This document exists for the benefit of anyone who wants to generate a new
sets of docs for the GitHub pages for this project.

**Should git ask you to set up your credentials, please follow the instructions it gives you and use your real name and your DataSift email address. Do not use pseudonyms or private email addresses.**

1. Make sure that git is installed on your system:

    `git --version`

    If you are using Ubuntu, you can install it using this command:

    `sudo apt-get install git`

2. Make sure that the documentation generator environment is installed on your system.  You can install it using these commands:

    `sudo apt-get install php5-cli`

    `sudo apt-get install php5-xsl`

    `sudo apt-get install php5-intl`

    `sudo apt-get install graphviz`

    `sudo apt-get install wget`

3. Create a temporary directory

    `mkdir tmp`

4. Change the current working directory

    `cd tmp`

5. Clone the DataSift PHP Client Library into master directory

    `git clone https://github.com/datasift/datasift-php.git master`

6. Clone the DataSift PHP Client Library into gh-pages directory

    `git clone https://github.com/datasift/datasift-php.git gh-pages`

7. Create a phpdoc directory

    `mkdir phpdoc`

8. Change the current working directory

    `cd phpdoc`

9. Install phpDocumentor2. Don't ask...

    `wget https://raw.github.com/phpDocumentor/phpDocumentor2/develop/installer.php`

    `sudo php installer.php`

10. Make a note of the path to the phpdoc command and use that in the next command.  On the author's machine it was `/home/ubuntu/tmp/php/bin/phpdoc.php`, check what it is on yours:

11. Run phpdoc.php twice:

    `sudo php ./bin/phpdoc.php -d ../master -t ../gh-docs`
    
10. Change the current working directory to gh-pages

    `cd ../gh-pages`

11. Switch to the gh-pages branch

    `git checkout gh-pages`

12. Copy documents into gh-pages:

    `cp -a ../gh-docs/* .`

13. Stage new documentation in git

    `git add *`

14. Commit the new documentation

    `git commit -m "Include a meaningful description here."`

15. Push changes to github

    `git push origin gh-pages`

16. That's it! you can delete the temporary directory now.

    `cd ../..`

    `rm -rf tmp`
