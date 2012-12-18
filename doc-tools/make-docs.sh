#!/bin/sh -v
if [ -z "$1" ]; then
    echo 'You must run this script with branch name as its argument, e.g.'
    echo 'sh ./make-docs.sh master'
    exit
fi
echo 'working on branch '$1

echo 'installing tools'
sudo apt-get install git
sudo apt-get install php5-cli
sudo apt-get install php5-xsl
sudo apt-get install php5-intl
sudo apt-get install graphviz
sudo apt-get install wget
echo 'making temporary directory'
mkdir tmp
cd tmp
echo 'cloning repos'
git clone https://github.com/datasift/datasift-php.git code
git clone https://github.com/datasift/datasift-php.git gh-pages
cd code
git checkout $1
cd ..
cd gh-pages
git checkout gh-pages
cd ..
echo 'making tmp/phpdoc'
mkdir phpdoc
cd phpdoc
echo 'installing phpDocumentor'
wget https://raw.github.com/phpDocumentor/phpDocumentor2/develop/installer.php
sudo php installer.php
sudo cp ../gh-pages/doc-tools/phpdoc.tpl.xml ./data/
sudo php `pwd`/bin/phpdoc.php -d ../code -t ../gh-docs
sudo php `pwd`/bin/phpdoc.php -d ../code -t ../gh-docs
cp -a ../gh-docs/* ../gh-pages/
cd ../gh-pages
git add *
git commit -m 'Updated to reflect the latest changes to '$1
echo 'You are going to update the gh-pages branch to reflect the latest changes to '$1
git push origin gh-pages
echo 'finished'
