//USE ESP8266 SOFTWARE VERSION 2.5.2 , NO ERROR


//LOAD LIBRARY
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>


int sensorValue;
#define relayPin 15 //
#define echoPin D7 // Echo Pin
#define trigPin D6 // Trigger Pin
long duration, distance; // Duration used to calculate distance

#define LED_MERAH 4
#define LED_KUNING 0
#define LED_HIJAU 2

#define DEVICE_ID 16

//SSID of your network
char ssid[] = "Veteran 2"; //SSID of your Wi-Fi router
char pass[] = "februari"; //Password of your Wi-Fi router
String server_ip =  "192.168.1.82:8080";
String server_path =  "";
#define SERVER_OK 200
#define SERVER_MAINTENANCE 400

bool is_check_distance = true;
bool is_connect_server = false;
int counter = 0;


void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600); // starts the serial port at 9600
  pinMode(relayPin,OUTPUT);
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  pinMode(LED_MERAH, OUTPUT);
  pinMode(LED_KUNING, OUTPUT);
  pinMode(LED_HIJAU, OUTPUT);

// Connect to Wi-Fi network
  send_action_device("DEVICE_ON");

  Serial.println();
  Serial.println();
  Serial.print("Connecting to...");
  Serial.println(ssid);

  WiFi.begin(ssid, pass);

  while (WiFi.status() != WL_CONNECTED) {
    merah_kedip();
    delay(200);
  }
  Serial.println("");
  Serial.println("Wi-Fi connected successfully");
  Serial.println("");
  Serial.print("Connected, IP address: ");
  Serial.println(WiFi.localIP());
  send_action_device("CONNECTED_NETWORK");

}

void loop() {

  //CHECK KONDISI JARINGAN TERLEBIH DAHULU
  if((WiFi.status() == WL_CONNECTED)){
    if(is_connect_server){
      hijau_terus();
    }else {
      merah_terus();
    }
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
    send_action_device("WATER_FILLING_ON");
  } else { 
    //OFF POMPA AIR
    digitalWrite(relayPin,LOW);
    send_action_device("WATER_FILLING_OFF");
    //SEND STATUS KE SERVER
    
    //CHECK KONDISI KEJERNIHAN AIR
    if(check_avoidance() == 1){ //JIKA AIR MASIH KERUH
      is_check_distance = false;
      //NYALAKAN PROSES ELEKTROKOAGULASI
      //...nyalakan relay untuk elektrokoagulasi
      kuning_terus();

      //SEND STATUS KE SERVER
      send_action_device("FILTERING_ON");
    } else {
      //LANJUTKAN CHECK JARAK
      is_check_distance = true;
      //MATIKAN PROSES ELEKTROKOAGULASI
      kuning_mati();


      //SEND STATUS KE SERVER
      send_action_device("FILTERING_OFF");
    }
  }
  
  counter += 500;
  delay(500);

  
}



bool check_avoidance(){
  sensorValue = analogRead(A0); // read analog input pin 0
  if(sensorValue < 80) {
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
    HTTPClient http;

    http.begin("http://"+server_ip+"/"+server_path+"post_sensor.php"); //HTTP
    //http.addHeader("Content-Type", "application/json");
    http.addHeader("Content-Type", "text/plain");

  
    String param = "{ \"water_level\" : " + String(distance,DEC) + ", \"turbidity\" : "+ String(sensorValue,DEC)+" , \"device_id\" : "+ DEVICE_ID +"}";
    int httpCode = http.POST(param);

    if(httpCode > 0){
       // file found at server
        if (httpCode == HTTP_CODE_OK) {
          is_connect_server = true;
            String payload = http.getString(); 
        }
        else {
            Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(httpCode).c_str());
        }
    }else{
      is_connect_server = false;
      Serial.printf("HTTP POST FAILED , error: %s\n",http.errorToString(httpCode).c_str());
    }
    http.end();
}

void send_action_device(String action){
    HTTPClient http;
    http.begin("http://"+server_ip+"/"+server_path+"post_action.php"); //HTTP
    //http.addHeader("Content-Type", "application/json");
    http.addHeader("Content-Type", "text/plain");

    String param = "{ \"type\" : \"" + action +  "\", \"device_id\" : "+ String(DEVICE_ID) + " }";
    int httpCode = http.POST(param);
    if(httpCode > 0){
       // file found at server
        if (httpCode == HTTP_CODE_OK) {
          is_connect_server = true;
            String payload = http.getString(); 
            Serial.println(payload);
        }
        else {
            Serial.printf("[HTTP] POST... failed, error: %s\n", http.errorToString(httpCode).c_str());
        }
    }else{
      is_connect_server = false;
      Serial.printf("HTTP POST FAILED , error: %s\n",http.errorToString(httpCode).c_str());
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

