# SAML Login
Container docker sample-scuola integrato con SimpleSAMLphp
che permette ai siti delle scuole di svolgere la funzionalità
di Identity Provider (IDp) SAML per i servizi Google.
##

###Componenti:
- Modulo Joomla! `SAML Login`:
    - Mostra una pagina di login agli utenti che si stanno autenticando
    con SAML, e ne gestisce il logout.
- Authentication source di SimpleSAMLphp `JoomlaAuth`:
    - Dirige l'utente alla pagina di login del modulo `SAML Login` e
     alla pagina di Google che stava cercando di visitare.
     All'untente verrà richiesto di inserire le credenziali di Joomla!
     per poter proseguire.
- Plugin Joomla! Google Sync:
    - Sincronizza gli utenti Joomla! con Google.
##

###Installazione:
Per eseguire questa applicazione sono necessari `Docker` e `docker-compose`:
  - https://docs.docker.com/install/
  - https://docs.docker.com/compose/install/

Configurare le variabili d'ambiente del container:
  1. `$ cp .env.template .env` e modificare `.env`
##

##Login SAML
###Testare la soluzione localmente:
  1. Aggiungere la seguente configurazione al proprio file hosts:

         127.0.0.1 localhost       #(Solo se non già presente)
         127.0.0.1 saml.localhost

  2. Avviare i container con `$ docker-compose up`
  3. Joomla!, Joomla! Admin e SimpleSAMLphp saranno accessibili rispettivamente a:
     - http://localhost/
     - http://localhost/administrator
     - http://saml.localhost/simplesaml (admin@admin)
  4. Installare il componente `SAML Login` accedendo al pannello
  admin di Joomla! e cliccando su:
  `Extensions > Manage > Install > Install from folder > Check and install`
  5. Modificare il campo `redirect_url` nel file `simplesamlphp/config/authsources.php`.
    `redirect_url => http://localhost/index.php?option=com_samllogin`
    (localhost è il dominio dell'installazione di Joomla!)
  6. È possibile testare il componente `JoomlaAuth` accedendo al pannello admin
  di SimpleSAMLphp e cliccando su:
  `Autenticazione > Prova le fonti di autenticazione configurate > joomlamodule:JoomlaAuth`
##

###Utilizzo con Google Suite:
  1. Generare i certificati RSA per SAML:
    `sh generate-cert.sh`
    Verranno generati due file nella cartella `cert`:
    `googleappsidp.pem` e `googleappsidp.crt`
  2. Configurare autenticazione con Joomla!:
    Modificare i campi `redirect_url` e `verify_url` nel file `simplesamlphp/config/authsources.php`.
    `redirect_url`: Url in cui si trova la pagina di login del modulo Joomla! `SAML Login`.
    (Sarà necessario sostituire a DOMAIN_NAME il dominio dell'account Google da configurare).
    `verify_url`: Url chiamato da SimpleSAMLphp per ottenere gli attributi di un
    utente, dopo che questo si è loggato su Joomla!. (Può essere necessario cambiare l'hostname
    se SimpleSAMLphp e Joomla! non sono hostati sulla stessa macchina).
  3. Sostituire a DOMAIN_NAME il dominio dell'account Google da configurare nel file
  `simplesamlphp/metadata/saml20-sp-remote.php`
  4. Configurare l'account Google:
      1. Collegarsi ad `admin.google.com` e accedere alla sezione "Sicurezza"
      2. Cliccare su "Imposta single-sign-on (SSO)"
  5. Abilitare la spunta "Configura SSO con provider di identità di terze parti"
  6. Inserire i seguenti valori (Sostituendo a DOMAIN_NAME il valore appropriato):
      `URL pagina di accesso` -> `http://saml.DOMAIN_NAME/simplesaml/saml2/idp/SSOService.php`
      `URL della pagina di uscita` -> `http://saml.DOMAIN_NAME/simplesaml/module.php/core/authenticate.php?as=joomlamodule%3AJoomlaAuth&logout`
  7. Caricare il certificato `googleappsidp.crt` generato in precedenza e salvare.
  8. Avviare i container con `$ docker-compose up`
  9. Per ulteriori informazioni, consultare: https://simplesamlphp.org/docs/stable/simplesamlphp-googleapps

##Plugin Google Sync
###Testare la soluzione localmente:
  1. Aspettare che composer installi le librerie di Google (verrà mostrato un messaggio nel terminale)
  2. Installare il componente `google-sync` accedendo al pannello
  admin di Joomla" e cliccando su:
  `Extensions > Manage > Install > Install from folder > Check and install`
  2. Abilitare e configurare il componente accedendo al pannello
  admin di Joomla! e cliccando su: `Extensions > Plugins > User - SOS Google Sync`
##

###Creazione di utenti e gruppi:
  1. Creando utenti e gruppi tramite Joomla!, ne verrà creata una copia su Google
  in automatico.
  Nella creazione dei gruppi, bisognerà specificare sia un nome che una mail: per
  fare ciò, inserire nella casella `Etichetta Gruppo` il valore `nome gruppo@mail`.
  Esempio: `Gruppo Docenti@docenti` creerà su Joomla! e su Google il gruppo `Gruppo Docenti`
  con mail `docenti@dominioscuola.it`
##

###Credenziali Google e utilizzo in Production:
  1. Le credenziali google sono già presenti nel sito di ogni scuola nel file `scuola/administrator/components/com_sos_users/credentials.json`.
  Per generarne di nuove, creare un account di servizio (https://cloud.google.com/iam/docs/creating-managing-service-accounts) e
  assegnargli gli scope `https://www.googleapis.com/auth/admin.directory.user` e `https://www.googleapis.com/auth/admin.directory.group` (https://support.google.com/a/answer/162106?hl=en)
  2. Per utilizzare il plugin sulle scuole esistenti, sarà necessario installare le librerie
  di Google per ogni scuola:
  Eseguire `composer require google/apiclient:"^2.0" --ignore-platform-reqs` nella root directory del sito delle scuole.
  3. Il sistema di gestione gruppi di questo plugin __non è__ retrocompatibile con il vecchio modulo.
  Per ottenere la retrocompatibilità, sarà necessario rinominare i gruppi esistenti __prima__ di installare il plugin.
  Esempio: In una scuola è presente un gruppo chiamato `Gruppo Docenti` con assegnata la mail `docenti@dominioscuola.it`
  tramite il vecchio modulo sos_users. Per rendere questo gruppo utilizzabile con il nuovo plugin, sarà necessario
  rinominarlo `Gruppo Docenti@docenti` prima di installare il plugin.
