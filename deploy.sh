MODULESLUG="magento2"
CURRENTDIR=`pwd`
MAINFILE="app/code/EMS/Pay/etc/module.xml"
# git config
GITPATH="$CURRENTDIR/" # this file should be in the base of your git repository

# git config
GITHUBPATH="/tmp/$MODULESLUG" # path to a temp GIT repo. No trailing slash required and don't add trunk.
GITHUBURL="https://github.com/emspay/$MODULESLUG" # Remote Github repo, with no trailing slash
GITUSER="emspay" # your git username

# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy magento2 module"
echo
echo ".........................................."
echo


# Check version in readme.txt is the same as module file after translating both to unix line breaks to work around grep's failure to identify mac line breaks
NEWVERSION1=`grep "^Stable tag:" $GITPATH/readme.txt | awk -F' ' '{print $NF}'`
echo "readme.txt version: $NEWVERSION1"
echo "$GITPATH$MAINFILE"
NEWVERSIONTMP=`grep 'setup_version="' "$GITPATH$MAINFILE" | awk -F' ' '{print $NF}'`
NEWVERSION2=$(echo $NEWVERSIONTMP| cut -d"\"" -f 2)
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" != "$NEWVERSION2" ]; then echo "Version in readme.txt & $MAINFILE don't match. Exiting...."; exit 1; fi

echo "Versions match in readme.txt and $MAINFILE. Let's proceed..."


if git show-ref --tags --quiet --verify -- "refs/tags/$NEWVERSION1"
    then
		echo "Version $NEWVERSION1 already exists as git tag. Exiting....";
		exit 1;
	else
		echo "Git version does not exist. Let's proceed..."
fi

cd $GITPATH
echo -e "Enter a commit message for this new version: \c"
read COMMITMSG
echo " ">>readme.txt
echo "= $NEWVERSION2 =">>readme.txt
echo "* $COMMITMSG">>readme.txt

git commit -am "$COMMITMSG"

echo "Tagging new version in git"
git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"

echo "Pushing latest commit to origin, with tags"
# original repo is an origin
git push origin master
git push origin master --tags
echo

chmod -R a+x /tmp

echo "Creating local copy of github repo ..."
git clone $GITHUBURL $GITHUBPATH

chmod -R a+x /tmp

echo "Exporting the HEAD of master from git to the trunk of github"
git checkout-index -a -f --prefix=$GITHUBPATH/trunk/

chmod -R a+x /tmp

echo "Ignoring github specific files and deployment script"
echo "$GITHUBPATH/trunk/deploy.sh">>.gitignore
echo "$GITHUBPATH/trunk/README.md">>.gitignore
echo "$GITHUBPATH/trunk/.git">>.gitignore
echo "$GITHUBPATH/trunk/.gitignore">>.gitignore

# echo "$GITHUBPATH/trunk/">>.gitignore

echo "$GITHUBPATH/trunk/includes/core/tests">>.gitignore

#git rm --force "$GITHUBPATH/trunk/includes/core/tests"

chmod -R a+x /tmp

echo "Changing directory to GIT and committing to trunk"
cd $GITHUBPATH/trunk/
# Add all new files that are not set to be ignored
git status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs git add .
git -c user.name="$GITUSER" commit -m "$COMMITMSG"

echo "Creating new GIT tag & committing it"
cd $GITHUBPATH
mv trunk/ tags/$NEWVERSION1/
cd $GITHUBPATH/tags/$NEWVERSION1
git -c user.name="$GITUSER" commit -m "Tagging version $NEWVERSION1"

echo "Removing temporary directory $GITHUBPATH"
rm -fr $GITHUBPATH/

echo "*** FIN ***"
