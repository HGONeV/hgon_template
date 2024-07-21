# Compile CSS
Install notejs

# Install:
npm install less -g
npm install less-plugin-clean-css -g
npm install -g less-plugin-autoprefix

# CSS generieren
## In Order wechseln
web\typo3conf\ext\hgon_template\Resources\Private\Styles
## Befehl ausführen
lessc -clean-css --autoprefix=">1%" styles.less ../../Public/Styles/styles.css