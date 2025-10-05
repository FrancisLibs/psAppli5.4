const Encore = require("@symfony/webpack-encore");

// Configurer l’environnement si nécessaire
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // Dossier de sortie
  .setOutputPath("public/build/")
  .setPublicPath("/build")

  // Entrées JS/CSS
  .addEntry("app", "./assets/app.js")
  .addEntry("editDeliveryNote", "./assets/JS/editDeliveryNote.js")
  .addEntry("stockValueJschart", "./assets/JS/stockValueJschart.js")
  //.addEntry("costPerMonthJschart", "./assets/JS/costPerMonthJschart.js")
  .addEntry("showMachine", "./assets/styles/showMachine.css")
  .addEntry("register", "./assets/styles/register.css")
  .addEntry("on_call_new", "./assets/styles/on_call_new.css")
  .addEntry("registration", "./assets/styles/registration.css")
  .addEntry("filterIndex", "./assets/JS/filterIndexEntry.js")

  // Symfony UX Stimulus
  .enableStimulusBridge("./assets/controllers.json")

  // Optimisations
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction());

// Autres options (décommenter si nécessaire)
//.enableSassLoader()
//.enableTypeScriptLoader()
//.enableReactPreset()
//.autoProvidejQuery()
//.enableIntegrityHashes(Encore.isProduction())

module.exports = Encore.getWebpackConfig();
