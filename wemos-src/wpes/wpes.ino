
//USE ESP8266 SOFTWARE VERSION 2.5.2 , NO ERROR

//LOAD LIBRARY
#include <EEPROM.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266WebServer.h>

#define relayPin 15 //
#define echoPin D7 // Echo Pin
#define trigPin D6 // Trigger Pin
int sensorLightValue;
long duration, distance; // Duration used to calculate distance

#define LED_MERAH 4
#define LED_KUNING 0
#define LED_HIJAU 2

#define DEVICE_ID 16

//SSID of your network
const char *ssidAP = "WPES-Config"; //ap name used for config esp
const char *passAP = "12345678"; //used as ap password for config esp
/* Soft AP network parameters */
IPAddress apIP(192, 168, 4, 1);
IPAddress netMsk(255, 255, 255, 0);

/* Don't set this wifi credentials. They are configurated at runtime and stored on EEPROM */
char ssid[32] = "";
char password[32] = "";
char accessKey[32] = "";
char deviceId[32] = "";
String server_ip =  "192.168.1.82:8080";
String server_path =  "";

#define SERVER_OK 200
#define SERVER_MAINTENANCE 400

bool is_check_distance = true;
bool is_connect_to_server = false;

bool must_connect_wifi;

/** Last time I tried to connect to WLAN */
long lastConnectTry = 0;
int counter = 0;

ESP8266WebServer server(80);

/** Current WLAN status */
int status = WL_IDLE_STATUS;


void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600); // starts the serial port at 9600

// Connect to Wi-Fi network

  WiFi.mode(WIFI_AP_STA);
  WiFi.softAP(ssidAP,passAP);
  //Start webserver
  server.on("/",handleRoot );
  server.on("/inputCode",handleInputCode);
  server.on("/wifi", handleWifi);
  server.on("/wifisave", handleWifiSave);
  server.on("/regisDevice/",handleForm);
  server.onNotFound(handleNotFound);
  server.begin();
  loadCredentials(); // Load WLAN credentials from network
  must_connect_wifi = strlen(ssid) > 0;//Request WLAN connect if there is a SSID


    //SET PIN CONFIGURATION
  pinMode(relayPin,OUTPUT);
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  pinMode(LED_MERAH, OUTPUT);
  pinMode(LED_KUNING, OUTPUT);
  pinMode(LED_HIJAU, OUTPUT);

}

void connectWifi() {
  Serial.println("Connecting as wifi client...");
  WiFi.disconnect();
  WiFi.begin ( ssid, password );
  int connRes = WiFi.waitForConnectResult();
  Serial.print ( "connRes: " );
  Serial.println ( connRes );
  Serial.println("");  
}

void loop() {
  if(must_connect_wifi){
    Serial.println("Connect Requested");
    WiFi.disconnect();
    must_connect_wifi = false;
    connectWifi();
    lastConnectTry = millis();
  } else {
    int s = WiFi.status();
    if (s == 0 && millis() > (lastConnectTry + 60000) ) {
      /* If WLAN disconnected and idle try to connect */
      /* Don't set retry time too low as retry interfere the softAP operation */
      must_connect_wifi = true;
    }
    if (status != s) { // WLAN status change
      Serial.print ( "Status: " );
      Serial.println ( s );
      status = s;
      if (s == WL_CONNECTED) {
        Serial.print ( "Connected to " );
        Serial.println ( ssid );
        Serial.print ( "IP address: " );
        Serial.println ( WiFi.localIP() );
              hijau_terus();

        if(accessKey ==  "NULL" || deviceId  == "NULL"){
          send_action_device("DEVICE_ON");
          send_action_device("CONNECTED_NETWORK");
        }
        
      } else if(s == WL_NO_SSID_AVAIL) {
        merah_terus();
        WiFi.disconnect();
      }
    }
  }

  //HTTP CONNECTION
  server.handleClient();


  //CHECK KONDISI JARINGAN TERLEBIH DAHULU
  if((status == WL_CONNECTED)){
    //ACT SEND VALUE SENSOR TO SERVER
    //.....
      if(counter == 5000) {
        send_sensor_value();
        counter = 0;
      }
  } else {
    Serial.println("Connecting");
    merah_kedip();
    if(counter == 5000) {
      counter = 0;
    }
  }


  //CHECK REALTIME TINGGI AIR
  if(check_distance() > 10 && is_check_distance){ //JIKA JARAK AIR KE SENSOR LEBIH DARI 30CM DAN CEK JARAK TRUE
    //ON POMPA AIR
    digitalWrite(relayPin,HIGH);

    //SEND STATUS KE SERVER
    if(accessKey ==  "NULL" || deviceId  == "NULL"){
      send_action_device("WATER_FILLING_ON");
    }
  } else { 
    //OFF POMPA AIR
    digitalWrite(relayPin,LOW);
    if(accessKey ==  "NULL" || deviceId  == "NULL"){
      send_action_device("WATER_FILLING_OFF");
    }
    //SEND STATUS KE SERVER
    
    //CHECK KONDISI KEJERNIHAN AIR
    if(check_avoidance() == 1){ //JIKA AIR MASIH KERUH
      is_check_distance = false;
      //NYALAKAN PROSES ELEKTROKOAGULASI
      //...nyalakan relay untuk elektrokoagulasi
      kuning_terus();

      //SEND STATUS KE SERVER
      if(accessKey ==  "NULL" || deviceId  == "NULL"){
       send_action_device("FILTERING_ON");
       }
    } else {
      //LANJUTKAN CHECK JARAK
      is_check_distance = true;
      //MATIKAN PROSES ELEKTROKOAGULASI
      kuning_mati();


      //SEND STATUS KE SERVER
       if(accessKey ==  "NULL" || deviceId  == "NULL"){
          send_action_device("FILTERING_OFF");
       }
    }
  }
  
  counter += 1000;
  delay(500);
  
}



bool check_avoidance(){
  sensorLightValue = analogRead(A0); // read analog input pin 0
  if(sensorLightValue < 80) {
    //digitalWrite(relayPin,HIGH);
    return false;
  }else {
    //digitalWrite(relayPin,LOW);
    return true;
  }
}


long check_distance(){
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  duration = pulseIn(echoPin, HIGH);
  //Calculate the distance (in cm) based on the speed of sound.
  distance = duration/58.2;
  //Serial.println(distance);
  delay(50);
  return distance;
  //Delay 50ms before next reading.
}


void send_sensor_value(){
    if(accessKey == "NULL" || deviceId == "NULL"){
      return;
    }
    HTTPClient http;

    http.begin("http://"+server_ip+"/"+server_path+"post_sensor.php"); //HTTP
    //http.addHeader("Content-Type", "application/json");
    http.addHeader("Content-Type", "text/plain");


    String param = "{ \"water_level\" : " + String(distance,DEC) + ", \"turbidity\" : "+ String(sensorLightValue,DEC)+" , \"device_id\" : "+ deviceId +"}";
    Serial.println(param);
    int httpCode = http.POST(param);

    if(httpCode > 0){
       // file found at server
        if (httpCode == HTTP_CODE_OK) {
          is_connect_to_server = true;
        }
        else {
            //Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(httpCode).c_str());
        }
    }else{
      is_connect_to_server = false;
      //Serial.printf("HTTP POST FAILED , error: %s\n",http.errorToString(httpCode).c_str());
    }
    http.end();
}

void send_action_device(String action){
    if(accessKey == "NULL" || deviceId == "NULL"){
      return;
    }
    HTTPClient http;
    http.begin("http://"+server_ip+"/"+server_path+"post_action.php"); //HTTP
    //http.addHeader("Content-Type", "application/json");
    http.addHeader("Content-Type", "text/plain");

    String param = "{ \"type\" : \"" + action +  "\", \"device_id\" : "+ String(deviceId) + " }";
    int httpCode = http.POST(param);
    if(httpCode > 0){
       // file found at server
        if (httpCode == HTTP_CODE_OK) {
          is_connect_to_server = true;
        }
        else {
            //Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(httpCode).c_str());
        }
    }else{
      is_connect_to_server = false;
      //Serial.printf("HTTP POST FAILED , error: %s\n",http.errorToString(httpCode).c_str());
    }
    http.end();
}

String authenticate(String code){
   HTTPClient http;
    http.begin("http://"+server_ip+"/"+server_path+"authenticate.php"); //HTTP
    //http.addHeader("Content-Type", "application/json");
    http.addHeader("Content-Type", "text/plain");

    String param = "{ \"code\" : \"" + code +  "\" }";
    Serial.println(param);
    int httpCode = http.POST(param);
    if(httpCode > 0){
       // file found at server
        if (httpCode == HTTP_CODE_OK) {
          is_connect_to_server = true;
          String payload = http.getString();
          Serial.println(payload);
        }
        else {
            //Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(httpCode).c_str());
        }
    }else{
      is_connect_to_server = false;
      //Serial.printf("HTTP POST FAILED , error: %s\n",http.errorToString(httpCode).c_str());
    }
    http.end();
}



void merah_terus(){ //tidak terhubung ke jaringan
  digitalWrite(LED_HIJAU,LOW);
  digitalWrite(LED_MERAH,HIGH);
}


void merah_kedip(){ //tidak terhubung ke server tetapi sudah terhubung ke jaringan
    digitalWrite(LED_HIJAU,LOW);
    digitalWrite(LED_KUNING, LOW);
    digitalWrite(LED_MERAH,HIGH);
    delay(100);
    digitalWrite(LED_MERAH,LOW);
    delay(100); 
}

void hijau_terus(){ //jika sudah terhubung ke server
  digitalWrite(LED_HIJAU,HIGH);
  digitalWrite(LED_MERAH,LOW);
  digitalWrite(LED_KUNING, LOW);

}


void kuning_terus(){
  digitalWrite(LED_KUNING, HIGH);
}

void kuning_mati(){
    digitalWrite(LED_KUNING, LOW);
}

