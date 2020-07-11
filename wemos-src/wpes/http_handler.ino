
const String regisForms = "<html>\
  <head><title>WPES Device Configuration - Registration Device</title></head>\
  <body><h1>Masukkan Kode Registrasi Perangkat WPES dari Aplikasi WPES</h1>\
  <form method=\"post\" enctype=\"application/x-www-form-urlencoded\" action=\"/regisDevice/\">\
  <input type=\"text\" name=\"code\" placeholder=\"XXXX-XXXX-XXXX\"><br><br>\
  <input type=\"submit\" value=\"Submit\"></form></body></html>";


const String homePage = "<html>\
  <head><title>WPES Device Configuration - Home Config</title></head>\
  <body><h1>Halaman Utama Konfigurasi perangkat WPES</h1>\
  <a href=\"wifi\" > Konfigurasi WIFI </a> <br>\
  <a href=\"inputCode\"> Masukkan kode registrasi perangkat</a>\
  </body></html>";

void handleRoot(){
  server.send(200,"text/html",homePage);
}

void handleInputCode(){
  if(status == 3){
     server.send(200,"text/html",regisForms);
  } else {
    server.send(200,"text/html","Perangkat WPES Belum tersambung ke internet silahkan konfigurasi Wifi terlebih dahulu <a href=\"wifi\"> Disini</a>");
  }
}

void handleForm(){
  if(server.method() != HTTP_POST){
    server.send(405, "text/plain", "Method Not Allowed");
  } else {
    String code = "";
    for (uint8_t i = 0;i < server.args(); i++){
      if(server.argName(i) == "code"){
        code = server.arg(i);
        break;
      }
    }
    if(code != ""){
      authenticate(code);
      server.send(200,"text/plain",code);
    } else {
      server.sendHeader("Location", "inputCode", true);
      server.sendHeader("Cache-Control", "no-cache, no-store, must-revalidate");
      server.sendHeader("Pragma", "no-cache");
      server.sendHeader("Expires", "-1");
      server.send ( 302, "text/plain", "");  
    }
  }
}

void handleNotFound(){
  String msg = "File Not Found";
  server.send(404,"text/plain",msg);
}


/** Wifi config page handler */
void handleWifi() {
  server.sendHeader("Cache-Control", "no-cache, no-store, must-revalidate");
  server.sendHeader("Pragma", "no-cache");
  server.sendHeader("Expires", "-1");

  String data_send = "<html><head></head><body><h1>Wifi config</h1>";
  if (server.client().localIP() == apIP) {
   data_send += String("<p>Kamu terkoneksi ke AP untuk konfigurasi perangkat WPES: ") + ssidAP + String("</p>");
  } else {
    data_send +=  String("<p>Kamu terkoneksi ke WIFI: ") + ssid + String(" untuk konfigurasi perangkat WPES</p>");
  }
  data_send += "<form method='POST' action='wifisave'><h4>Connect to network:</h4>\
    <label>SSID</label><br>\
    <input type='text' placeholder='network' name='n' value='"+String(ssid)+"'/>\
    <br /><label>Password</label><br>\
    <input type='password' placeholder='password' name='p' value='"+String(password)+"'/>\
    <br /><input type='submit' value='Connect/Disconnect'/></form>\
    <p>You may want to <a href='/'>return to the home page</a>.</p>\
    </body></html>";

  
  server.send(200, "text/html", data_send); // Empty content inhibits Content-length header so we have to close the socket ourselves.
  
}

/** Handle the WLAN save form and redirect to WLAN config page again */
void handleWifiSave() {
  Serial.println("wifi save");
  server.arg("n").toCharArray(ssid, sizeof(ssid) - 1);
  server.arg("p").toCharArray(password, sizeof(password) - 1);
  server.sendHeader("Location", "wifi", true);
  server.sendHeader("Cache-Control", "no-cache, no-store, must-revalidate");
  server.sendHeader("Pragma", "no-cache");
  server.sendHeader("Expires", "-1");
  server.send ( 302, "text/plain", "");  // Empty content inhibits Content-length header so we have to close the socket ourselves.
  server.client().stop(); // Stop is needed because we sent no content length
  saveCredentials();
  must_connect_wifi = strlen(ssid) > 0; // Request WLAN connect with new credentials if there is a SSID
}
