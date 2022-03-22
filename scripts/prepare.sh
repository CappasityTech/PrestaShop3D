#!/usr/bin/env bash
set -ex
shopt -s extglob

BUILD_PATH=$(realpath -s ./build)

# recreate dirs
rm -rf "${BUILD_PATH}"
mkdir -p "${BUILD_PATH}"

RELEASE_VERSION=$1
SOURCE_DIR=cappasity3d
BUILD_SRC_PATH="$BUILD_PATH/$SOURCE_DIR"
ARTIFACT_NAME="v${RELEASE_VERSION}-${SOURCE_DIR}.zip"
VERSION_PLACEHOLDER="{VERSION_PLACEHOLDER}"
API_HOST_PLACEHOLDER="{API_HOST_PLACEHOLDER}"
LICENSE_PLACEHOLDER="{LICENSE_PLACEHOLDER}"
BUILD="$(date +%s)"
API_HOST=${API_HOST:-api.cappasity3d.com}
SRC_DIR=${SRC_DIR:-src}
PWD_DIR=$PWD

replace() {
  files=$(grep -rl "${1}" "$BUILD_SRC_PATH")
  set +e
  for file in $files;
  do
    sed -i '' -e "s/${1}/${2}/g" "$file"
  done
  set -e
}

replace_to_multiline() {
  files=$(grep -rl "${1}" "$BUILD_SRC_PATH")
  set +e
  for file in $files;
  do
    sed -i '' -e "/${1}/r ${2}" -e "/${1}/d" "$file"
  done
  set -e
}

rename() {
  for file in "${1}"/!(*.php)
  do
    name=$(basename "${file}")
    new_name="${BUILD}.${name}"
    dir_name=$(realpath $(dirname "${file}"))
    new_path="${dir_name}/${new_name}"

    mv "${file}" "${new_path}"
    replace "${name}" "${new_name}"
  done
}

# create source path
mkdir -p "${BUILD_SRC_PATH}"

# install deps
cd "$SRC_DIR"
./composer.phar -q install

# recursively add index.php to vendor directory (prestashop requirements)
find ./vendor/ -type d -exec cp ./index.php {} \;

# copy files
rsync \
  -avq \
  --exclude='composer.json' \
  --exclude='composer.lock' \
  --exclude='composer.phar' \
  --exclude="*.log" \
  . "${BUILD_SRC_PATH}"

cd "$PWD_DIR"
# set up version
replace $VERSION_PLACEHOLDER "$RELEASE_VERSION"
# set up api host
replace $API_HOST_PLACEHOLDER "$API_HOST"
# license
replace_to_multiline $LICENSE_PLACEHOLDER ./scripts/templates/license

# rename static files
rename $(realpath "${BUILD_SRC_PATH}/views/js")
rename $(realpath "${BUILD_SRC_PATH}/views/css")
rename $(realpath "${BUILD_SRC_PATH}/views/img")

cd "$BUILD_PATH"
zip -q -r -x='*.DS_Store*' $ARTIFACT_NAME $SOURCE_DIR
