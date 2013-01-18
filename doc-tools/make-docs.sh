#!/bin/bash
#-v

export BASE_DIR="`pwd`/"

source ${BASE_DIR}sub/make-docs-util-defs.sh
export BASE_DIR="/tmp/$(basename $0).$$.tmp/"
initialise $*

### PHP-specific parameters
parameters "php"

### installation of PHP-specific tools
message "installing tools"
sudo apt-get install git
sudo apt-get install php5-cli
sudo apt-get install php5-xsl
sudo apt-get install php5-intl
sudo apt-get install graphviz
sudo apt-get install wget

pre_build

### PHP-specific build steps

message "preparing to build documents"
export PHPDOC_DIR="${BASE_DIR}tmp/${LABEL}/phpdoc/"
export GH_DOCS_DIR="${BASE_DIR}tmp/${LABEL}/gh-docs/"
mkdir ${PHPDOC_DIR} ; stop_on_error

(
	#message "preparing documents build environment"
	message 'installing phpDocumentor'
	cd ${PHPDOC_DIR} ; stop_on_error
	wget https://raw.github.com/phpDocumentor/phpDocumentor2/develop/installer.php ; stop_on_error
) || error "stopped parent"

(
	message "building documents"
	cd ${PHPDOC_DIR} ; stop_on_error
	sudo php installer.php ; stop_on_error
	sudo cp ${GH_PAGES_DIR}doc-tools/phpdoc.tpl.xml ./data/ ; stop_on_error
	sudo php ${PHPDOC_DIR}bin/phpdoc.php -d ${CODE_DIR}lib -t ${GH_DOCS_DIR} ; stop_on_error
	sudo php ${PHPDOC_DIR}bin/phpdoc.php -d ${CODE_DIR}lib -t ${GH_DOCS_DIR} ; stop_on_error
) || error "stopped parent"
(
	message "copying documents"
	cd ${PHPDOC_DIR} ; stop_on_error
	cp -a ${GH_DOCS_DIR}* ${GH_PAGES_DIR} ; stop_on_error
) || error "stopped parent"

(
	cd ${GH_PAGES_DIR} ; stop_on_error
	git add *
) || error "stopped parent"

post_build

finalise
