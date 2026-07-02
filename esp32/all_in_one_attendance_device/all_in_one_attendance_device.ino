
#include <WiFi.h>
#include <WebServer.h>
#include <Preferences.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <Wire.h>
#include <LittleFS.h>
#include <Adafruit_Fingerprint.h>
#include <Adafruit_PN532.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// ===================== PIN CONFIG =====================
#define OLED_SDA 8
#define OLED_SCL 9

#define PN532_SCK 1
#define PN532_MISO 2
#define PN532_MOSI 3
#define PN532_SS 4

#define FP_RX 6
#define FP_TX 7

#define BUZZER_PIN 10
#define BUTTON_PIN 20

#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1

// ===================== DEFAULT SETUP HOTSPOT =====================
const char* SETUP_AP_SSID = "Attendance_Device_Setup";
const char* SETUP_AP_PASSWORD = "12345678";

// ===================== OBJECTS =====================
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);
HardwareSerial fingerSerial(1);
Adafruit_Fingerprint finger = Adafruit_Fingerprint(&fingerSerial);
Adafruit_PN532 nfc(PN532_SS);

Preferences prefs;
WebServer server(80);

// ===================== SAVED SETTINGS =====================
String wifi_ssid = "";
String wifi_pass = "";
String wifi_ssid2 = "";
String wifi_pass2 = "";
String api_base_url = "";
String api_key = "";
String device_code = "ESP32-C3-DEVICE-001";
String device_token = "";
String device_secret = "";

unsigned long lastHeartbeat = 0;
const unsigned long HEARTBEAT_INTERVAL = 30000; // 30 seconds

int activeSessionId = 0;
String sessionAttendanceMethod = "both";

unsigned long lastButtonPress = 0;
unsigned long buttonDownTime = 0;
int buttonClickCount = 0;
bool buttonWasDown = false;
bool longPressHandled = false;

enum DeviceMode {
  MENU_MODE,
  FINGERPRINT_ATTENDANCE_MODE,
  RFID_ATTENDANCE_MODE,
  DASHBOARD_MODE,
  DEVICE_CAPTURE_MODE
};

DeviceMode currentMode = MENU_MODE;

// ===================== OLED =====================
void showMessage(String line1, String line2 = "", String line3 = "", String line4 = "") {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);

  display.setCursor(0, 0);
  display.println(line1);

  if (line2 != "") {
    display.setCursor(0, 16);
    display.println(line2);
  }

  if (line3 != "") {
    display.setCursor(0, 32);
    display.println(line3);
  }

  if (line4 != "") {
    display.setCursor(0, 48);
    display.println(line4);
  }

  display.display();
}

void showMainMenu() {
  showMessage(
    "ATTENDANCE",
    "1 Click: Finger",
    "2 Clicks: RFID",
    "Hold: Dashboard"
  );
}

// ===================== SETTINGS =====================
void loadSettings() {
  prefs.begin("attend", true);

  wifi_ssid = prefs.getString("ssid", "");
  wifi_pass = prefs.getString("pass", "");
  wifi_ssid2 = prefs.getString("ssid2", "");
  wifi_pass2 = prefs.getString("pass2", "");
  api_base_url = prefs.getString("url", "");
  api_key = prefs.getString("key", "CHANGE_THIS_SECRET_KEY_12345");
  device_code = prefs.getString("device", "ESP32-C3-DEVICE-001");
  device_token = prefs.getString("token", "");
  device_secret = prefs.getString("secret", "");

  prefs.end();
}

void saveSettings(String ssid, String pass, String url, String key, String device, String ssid2 = "", String pass2 = "", String token = "", String secret = "") {
  prefs.begin("attend", false);

  prefs.putString("ssid", ssid);
  prefs.putString("pass", pass);
  prefs.putString("ssid2", ssid2);
  prefs.putString("pass2", pass2);
  prefs.putString("url", url);
  prefs.putString("key", key);
  prefs.putString("device", device);
  prefs.putString("token", token);
  prefs.putString("secret", secret);

  prefs.end();
}

void clearSettings() {
  prefs.begin("attend", false);
  prefs.clear();
  prefs.end();
}

// ===================== SETUP PORTAL =====================
String escapeHtml(String s) {
  s.replace("&", "&amp;");
  s.replace("'", "&#39;");
  s.replace("\"", "&quot;");
  s.replace("<", "&lt;");
  s.replace(">", "&gt;");
  return s;
}

String setupPage() {
  String html = "";

  html += "<!DOCTYPE html><html><head><title>Attendance Device Setup</title>";
  html += "<meta name='viewport' content='width=device-width, initial-scale=1'>";
  html += "<style>";
  html += "body{font-family:Arial;background:#f4f4f4;padding:20px;}";
  html += ".box{background:#fff;padding:20px;border-radius:10px;max-width:500px;margin:auto;}";
  html += "input{width:100%;padding:12px;margin:8px 0;box-sizing:border-box;}";
  html += "button{width:100%;padding:12px;background:#007bff;color:white;border:0;border-radius:5px;font-size:16px;}";
  html += "h2{text-align:center;}";
  html += "</style></head><body>";
  html += "<div class='box'>";
  html += "<h2>Attendance Device Setup</h2>";
  html += "<form method='POST' action='/save'>";
  html += "<p style='font-size:12px;color:#666;'>Use 2.4GHz WiFi only (not 5GHz) | <a href='/scan' target='_blank' style='color:#007bff;'>Scan Networks</a></p>";
  html += "<label>WiFi Name (Primary)</label>";
  html += "<input name='ssid' value='" + escapeHtml(wifi_ssid) + "' required>";
  html += "<label>WiFi Password</label>";
  html += "<input name='pass' value='" + escapeHtml(wifi_pass) + "'>";
  html += "<label>WiFi Name (Backup)</label>";
  html += "<input name='ssid2' value='" + escapeHtml(wifi_ssid2) + "'>";
  html += "<label>Backup Password</label>";
  html += "<input name='pass2' value='" + escapeHtml(wifi_pass2) + "'>";
  html += "<label>API Base URL</label>";
  html += "<input name='url' value='" + escapeHtml(api_base_url) + "' placeholder='http://192.168.1.100/university_attendance_system/api' required>";
  html += "<label>Device Token</label>";
  html += "<input name='token' value='" + escapeHtml(device_token) + "' placeholder='Your device token from admin panel'>";
  html += "<label>Device Secret</label>";
  html += "<input name='secret' value='" + escapeHtml(device_secret) + "' placeholder='Your device secret from admin panel'>";
  html += "<label>API Key (Legacy)</label>";
  html += "<input name='key' value='" + escapeHtml(api_key) + "' placeholder='Only if using old shared API key'>";
  html += "<label>Device Code (Legacy)</label>";
  html += "<input name='device' value='" + escapeHtml(device_code) + "' placeholder='Only if using old shared API key'>";
  html += "<button type='submit'>Save Settings</button>";
  html += "</form>";
  html += "<br><form method='POST' action='/reset'><button style='background:#dc3545'>Reset Settings</button></form>";
  html += "</div></body></html>";

  return html;
}

void startSetupPortal() {
  showMessage(
    "Setup Portal",
    "SSID:",
    SETUP_AP_SSID,
    "IP: 192.168.4.1"
  );

  WiFi.mode(WIFI_AP);
  WiFi.softAP(SETUP_AP_SSID, SETUP_AP_PASSWORD);

  server.on("/", HTTP_GET, []() {
    server.send(200, "text/html", setupPage());
  });

  server.on("/save", HTTP_POST, []() {
    String ssid = server.arg("ssid");
    String pass = server.arg("pass");
    String ssid2 = server.arg("ssid2");
    String pass2 = server.arg("pass2");
    String url = server.arg("url");
    String key = server.arg("key");
    String device = server.arg("device");
    String token = server.arg("token");
    String secret = server.arg("secret");

    saveSettings(ssid, pass, url, key, device, ssid2, pass2, token, secret);

    server.send(
      200,
      "text/html",
      "<h2>Settings Saved</h2><p>Device will restart now.</p>"
    );

    showMessage("Settings Saved", "Restarting...");
    delay(2000);
    ESP.restart();
  });

  server.on("/scan", HTTP_GET, []() {
    int n = WiFi.scanComplete();
    if (n == -2) {
      WiFi.scanNetworks(true);
      server.send(200, "text/html", "<html><head><meta http-equiv='refresh' content='4;url=/scan'><title>Scanning...</title><style>body{font-family:Arial;padding:20px;}</style></head><body><h2>Scanning WiFi...</h2><p>Please wait 4 seconds.</p></body></html>");
      return;
    }
    if (n == -1) {
      server.send(200, "text/html", "<html><head><meta http-equiv='refresh' content='2;url=/scan'><title>Scanning...</title><style>body{font-family:Arial;padding:20px;}</style></head><body><h2>Scanning WiFi...</h2><p>Please wait...</p></body></html>");
      return;
    }

    String html = "<!DOCTYPE html><html><head><title>WiFi Scan</title><meta name='viewport' content='width=device-width,initial-scale=1'><style>body{font-family:Arial;padding:20px;background:#f4f4f4;}table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;}th,td{padding:10px;text-align:left;border-bottom:1px solid #ddd;}th{background:#007bff;color:#fff;}.ch{margin:5px;padding:3px 8px;border-radius:4px;font-size:12px;color:#fff;}.g{background:#28a745;}.o{background:#ff9800;}.r{background:#dc3545;}</style></head><body><h2>Available Networks</h2><p style='color:#888;'>Strong signal (green) = closer / better</p><table><tr><th>SSID</th><th>Signal</th><th>Security</th></tr>";
    for (int i = 0; i < n; i++) {
      String ssid = WiFi.SSID(i);
      if (ssid == "") continue;
      int rssi = WiFi.RSSI(i);
      String sigClass = rssi > -60 ? "g" : (rssi > -75 ? "o" : "r");
      String enc = WiFi.encryptionType(i) == 8 ? "Open" : "Secured";
      html += "<tr><td><strong>" + escapeHtml(ssid) + "</strong></td><td><span class='ch " + sigClass + "'>" + String(rssi) + " dBm</span></td><td>" + enc + "</td></tr>";
    }
    html += "</table><br><a href='/scan' style='color:#007bff;'>↻ Refresh</a> | <a href='/' style='color:#007bff;'>← Back</a></body></html>";
    WiFi.scanDelete();
    server.send(200, "text/html", html);
  });

  server.on("/reset", HTTP_POST, []() {
    clearSettings();

    server.send(
      200,
      "text/html",
      "<h2>Settings Cleared</h2><p>Device will restart now.</p>"
    );

    showMessage("Settings Cleared", "Restarting...");
    delay(2000);
    ESP.restart();
  });

  server.begin();

  while (true) {
    server.handleClient();
    delay(10);
  }
}

// ===================== HELPERS =====================
String getJsonValue(String json, String key) {
  String searchKey = "\"" + key + "\":\"";
  int startIndex = json.indexOf(searchKey);

  if (startIndex < 0) return "";

  startIndex += searchKey.length();
  int endIndex = json.indexOf("\"", startIndex);

  if (endIndex < 0) return "";

  String value = json.substring(startIndex, endIndex);
  value.replace("\\/", "/");
  value.replace("\\\"", "\"");

  return value;
}

String getJsonNumberValue(String json, String key) {
  String searchKey = "\"" + key + "\":";
  int startIndex = json.indexOf(searchKey);

  if (startIndex < 0) return "";

  startIndex += searchKey.length();

  int commaIndex = json.indexOf(",", startIndex);
  int braceIndex = json.indexOf("}", startIndex);
  int endIndex = commaIndex;

  if (endIndex < 0 || (braceIndex >= 0 && braceIndex < endIndex)) {
    endIndex = braceIndex;
  }

  if (endIndex < 0) return "";

  String value = json.substring(startIndex, endIndex);
  value.replace("\"", "");
  value.trim();

  return value;
}

String shortenText(String text, int maxLen) {
  if (text.length() <= maxLen) return text;
  return text.substring(0, maxLen);
}

void beepSuccess() {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(120);
  digitalWrite(BUZZER_PIN, LOW);
}

void beepError() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    delay(100);
  }
}

// ===================== WIFI =====================
bool tryConnect(String ssid, String pass) {
  if (ssid == "") return false;

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid.c_str(), pass.c_str());

  showMessage("Connecting", ssid.substring(0, 18));

  unsigned long startTime = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startTime < 15000) {
    delay(500);
    Serial.print(".");
  }

  return WiFi.status() == WL_CONNECTED;
}

bool connectWiFi() {
  if (wifi_ssid == "" && wifi_ssid2 == "" || api_base_url == "") {
    showMessage("No Config", "Starting Setup", "Portal...");
    delay(1500);
    return false;
  }

  if (tryConnect(wifi_ssid, wifi_pass)) {
    showMessage("WiFi Connected", WiFi.localIP().toString());
    delay(1000);
    return true;
  }

  if (tryConnect(wifi_ssid2, wifi_pass2)) {
    showMessage("WiFi 2 Connected", WiFi.localIP().toString());
    delay(1000);
    return true;
  }

  showMessage(
    "WiFi Failed",
    "1: " + wifi_ssid.substring(0, 12),
    "2: " + wifi_ssid2.substring(0, 12),
    "Status: " + String(WiFi.status())
  );
  delay(3000);
  return false;
}

// ===================== SYNC SETTINGS FROM SERVER =====================
void syncSettingsFromServer() {
  if (WiFi.status() != WL_CONNECTED) return;

  showMessage("Checking Remote", "Config...");
  HTTPClient http;
  String url = api_base_url + "/get_device_settings.php?";
  if (device_token != "") {
    url += "device_token=" + device_token;
  } else {
    url += "api_key=" + api_key + "&device_code=" + device_code;
  }

  http.begin(url);
  int httpCode = http.GET();
  String payload = http.getString();
  http.end();

  if (httpCode == 200 && payload.indexOf("\"success\":true") >= 0) {
    String serverSsid = getJsonValue(payload, "wifi_ssid_1");
    String serverPass = getJsonValue(payload, "wifi_pass_1");
    String serverSsid2 = getJsonValue(payload, "wifi_ssid_2");
    String serverPass2 = getJsonValue(payload, "wifi_pass_2");
    String serverUrl = getJsonValue(payload, "api_base_url");
    String serverKey = getJsonValue(payload, "api_key");

    bool changed = false;

    if (serverSsid != "" && serverSsid != wifi_ssid) {
      wifi_ssid = serverSsid;
      wifi_pass = serverPass;
      changed = true;
    }
    if (serverSsid2 != "" && serverSsid2 != wifi_ssid2) {
      wifi_ssid2 = serverSsid2;
      wifi_pass2 = serverPass2;
      changed = true;
    }
    if (serverUrl != "" && serverUrl != api_base_url) {
      api_base_url = serverUrl;
      changed = true;
    }
    if (serverKey != "" && serverKey != api_key) {
      api_key = serverKey;
      changed = true;
    }

    if (changed) {
      saveSettings(wifi_ssid, wifi_pass, api_base_url, api_key, device_code, wifi_ssid2, wifi_pass2);
      showMessage("Config Updated", "Restarting...");
      delay(1500);
      ESP.restart();
    }
  }
}

// ===================== ACTIVE SESSION =====================
void fetchActiveSession() {
  if (WiFi.status() != WL_CONNECTED) {
    showMessage("No WiFi", "Cannot Fetch", "Session");
    delay(1500);
    return;
  }

  HTTPClient http;
  String url = api_base_url + "/get_active_session.php?api_key=" + api_key + "&device_code=" + device_code;

  Serial.println(url);

  http.begin(url);
  int httpCode = http.GET();
  String payload = http.getString();
  http.end();

  Serial.println(payload);

  if (httpCode == 200 && payload.indexOf("\"success\":true") >= 0) {
    String sid = getJsonValue(payload, "session_id");

    if (sid == "") {
      sid = getJsonNumberValue(payload, "session_id");
    }

    activeSessionId = sid.toInt();

    sessionAttendanceMethod = getJsonValue(payload, "attendance_method");
    if (sessionAttendanceMethod == "") sessionAttendanceMethod = "both";

    showMessage(
      "Active Session",
      "ID: " + String(activeSessionId),
      "Method: " + sessionAttendanceMethod
    );

    delay(1500);
  } else {
    activeSessionId = 0;
    sessionAttendanceMethod = "both";
    showMessage("Attendance Closed", "Session Ended", "Start New Session");
    delay(2000);
  }
}

void pollActiveSession() {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  String url = api_base_url + "/get_active_session.php?";
  if (device_token != "") {
    url += "device_token=" + device_token;
  } else {
    url += "api_key=" + api_key + "&device_code=" + device_code;
  }
  http.begin(url);
  int httpCode = http.GET();
  String payload = http.getString();
  http.end();

  int newId = 0;
  if (httpCode == 200 && payload.indexOf("\"success\":true") >= 0) {
    String sid = getJsonValue(payload, "session_id");
    if (sid == "") sid = getJsonNumberValue(payload, "session_id");
    newId = sid.toInt();
  }

  if (newId > 0 && newId != activeSessionId) {
    activeSessionId = newId;
    sessionAttendanceMethod = getJsonValue(payload, "attendance_method");
    if (sessionAttendanceMethod == "") sessionAttendanceMethod = "both";
    showMessage("Session Active!", "ID: " + String(activeSessionId), "Method: " + sessionAttendanceMethod);
    delay(1000);
    showMainMenu();
  } else if (newId == 0 && activeSessionId > 0) {
    activeSessionId = 0;
    sessionAttendanceMethod = "both";
    showMessage("Session Ended");
    delay(1000);
    showMainMenu();
  }
}

// ===================== OFFLINE =====================
int countOfflineRecords() {
  if (!LittleFS.exists("/offline.txt")) return 0;

  File f = LittleFS.open("/offline.txt", "r");
  if (!f) return 0;

  int count = 0;

  while (f.available()) {
    String line = f.readStringUntil('\n');
    line.trim();
    if (line.length() > 0) count++;
  }

  f.close();
  return count;
}

void saveOffline(String sessionId, String type, String value) {
  File f = LittleFS.open("/offline.txt", "a");

  if (!f) {
    showMessage("Offline Error", "File Failed");
    beepError();
    delay(1500);
    return;
  }

  String uniqueId = String(esp_random(), HEX) + String(millis(), HEX);
  String timestamp = String(millis());
  f.println("{\"unique_id\":\"" + uniqueId + "\",\"session_id\":\"" + sessionId + "\",\"identifier_type\":\"" + type + "\",\"identifier_value\":\"" + value + "\",\"timestamp\":" + timestamp + "}");
  f.close();

  showMessage("No Internet", "Saved Offline", "Records: " + String(countOfflineRecords()), type + ": " + value);
  delay(2000);
}

void syncOfflineRecords() {
  if (WiFi.status() != WL_CONNECTED) return;

  int count = countOfflineRecords();
  if (count == 0) return;

  showMessage("Syncing", String(count) + " Offline", "Records...");

  if (!LittleFS.exists("/offline.txt")) return;

  File f = LittleFS.open("/offline.txt", "r");
  if (!f) return;

  String jsonArray = "[";
  bool first = true;
  while (f.available()) {
    String line = f.readStringUntil('\n');
    line.trim();
    if (line.length() > 0) {
      if (!first) jsonArray += ",";
      jsonArray += line;
      first = false;
    }
  }
  f.close();
  jsonArray += "]";

  if (jsonArray == "[]") return;

  HTTPClient http;
  String url = api_base_url + "/sync_offline.php";

  http.begin(url);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String postData;
  if (device_token != "") {
    postData = "device_token=" + device_token;
  } else {
    postData = "api_key=" + api_key + "&device_code=" + device_code;
  }

  postData += "&records=" + urlencode(jsonArray);

  int httpCode = http.POST(postData);
  String payload = http.getString();
  http.end();

  if (httpCode == 200 && payload.indexOf("\"success\":true") >= 0) {
    LittleFS.remove("/offline.txt");
    showMessage("Sync Complete", String(count) + " Records", "Synced Successfully");
    beepSuccess();
    delay(2000);
  } else {
    showMessage("Sync Failed", "Will Retry Later");
    beepError();
    delay(1000);
  }
}

String urlencode(String str) {
  String encoded = "";
  char c;
  char code0;
  char code1;
  for (int i = 0; i < str.length(); i++) {
    c = str.charAt(i);
    if (c == ' ') {
      encoded += '+';
    } else if (isalnum(c)) {
      encoded += c;
    } else {
      code1 = (c & 0xf) + '0';
      if ((c & 0xf) > 9) code1 = (c & 0xf) - 10 + 'A';
      c = (c >> 4) & 0xf;
      code0 = c + '0';
      if (c > 9) code0 = c - 10 + 'A';
      encoded += '%';
      encoded += code0;
      encoded += code1;
    }
  }
  return encoded;
}

// ===================== ATTENDANCE API =====================
bool sendAttendance(String sessionId, String type, String value) {
  if (WiFi.status() != WL_CONNECTED) {
    saveOffline(sessionId, type, value);
    beepError();
    delay(1500);
    return false;
  }

  HTTPClient http;
  String url = api_base_url + "/mark_attendance.php";

  http.begin(url);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String postData;
  if (device_token != "") {
    postData = "device_token=" + device_token;
  } else {
    postData = "api_key=" + api_key + "&device_code=" + device_code;
  }

  postData +=
    "&session_id=" + sessionId +
    "&identifier_type=" + type +
    "&identifier_value=" + value;

  Serial.println(postData);

  int httpCode = http.POST(postData);
  String payload = http.getString();

  http.end();

  Serial.println(payload);

  if (httpCode == 200 && payload.indexOf("\"success\":true") >= 0) {
    String studentName = getJsonValue(payload, "student_name");
    String matricNo = getJsonValue(payload, "matric_no");

    if (studentName == "") studentName = "Student";

    studentName = shortenText(studentName, 18);
    matricNo = shortenText(matricNo, 18);

    if (payload.indexOf("already") >= 0 || payload.indexOf("Already") >= 0) {
      showMessage("Already Taken", studentName, matricNo);
      beepSuccess();
      delay(2500);
      return true;
    }

    if (type == "rfid") {
      showMessage("Welcome", studentName, matricNo, "RFID Recorded");
    } else {
      showMessage("Welcome", studentName, matricNo, "Finger Recorded");
    }

    beepSuccess();
    delay(3000);
    return true;
  }

  if (payload.indexOf("Student not found") >= 0) {
    showMessage("Student Not", "Found", type, value);
  } else if (payload.indexOf("not registered") >= 0) {
    showMessage("Not Registered", "For Course", value);
  } else if (payload.indexOf("not active") >= 0) {
    showMessage("Session Not", "Active");
  } else if (payload.indexOf("not allowed") >= 0 || payload.indexOf("Not Allowed") >= 0) {
    showMessage("Method Not", "Allowed", type);
  } else {
    showMessage("API Failed", "Saved Offline");
    saveOffline(sessionId, type, value);
  }

  beepError();
  delay(2000);
  return false;
}

// ===================== FINGERPRINT =====================
int getFingerprintID() {
  uint8_t p = finger.getImage();
  if (p != FINGERPRINT_OK) return -1;

  p = finger.image2Tz();
  if (p != FINGERPRINT_OK) return -1;

  p = finger.fingerSearch();
  if (p != FINGERPRINT_OK) return -2;

  return finger.fingerID;
}

uint8_t enrollFingerprint(uint8_t id) {
  int p = -1;

  Serial.println("================================");
  Serial.print("Starting fingerprint enrollment ID #");
  Serial.println(id);
  Serial.println("================================");

  showMessage("Enroll Finger", "ID: " + String(id), "Get Ready...");
  delay(2000);

  showMessage("Step 1", "Place Finger", "Keep Still");

  while (p != FINGERPRINT_OK) {
    p = finger.getImage();

    if (p == FINGERPRINT_OK) {
      Serial.println("First image taken.");
    } else if (p == FINGERPRINT_NOFINGER) {
      Serial.println("Waiting for first finger...");
    } else {
      Serial.print("First image error: ");
      Serial.println(p);
    }

    delay(300);
  }

  p = finger.image2Tz(1);

  if (p != FINGERPRINT_OK) {
    Serial.print("First convert failed: ");
    Serial.println(p);
    showMessage("Convert Failed", "Try Again");
    beepError();
    delay(2500);
    return p;
  }

  Serial.println("First image converted.");

  showMessage("Step 2", "Remove Finger", "Wait...");
  delay(3000);

  p = 0;

  while (p != FINGERPRINT_NOFINGER) {
    p = finger.getImage();
    Serial.println("Waiting for finger removal...");
    delay(300);
  }

  showMessage("Step 3", "Place SAME", "Finger Again");
  delay(1500);

  p = -1;

  while (p != FINGERPRINT_OK) {
    p = finger.getImage();

    if (p == FINGERPRINT_OK) {
      Serial.println("Second image taken.");
    } else if (p == FINGERPRINT_NOFINGER) {
      Serial.println("Waiting for second finger...");
    } else {
      Serial.print("Second image error: ");
      Serial.println(p);
    }

    delay(300);
  }

  p = finger.image2Tz(2);

  if (p != FINGERPRINT_OK) {
    Serial.print("Second convert failed: ");
    Serial.println(p);
    showMessage("2nd Convert", "Failed");
    beepError();
    delay(2500);
    return p;
  }

  Serial.println("Second image converted.");

  showMessage("Creating", "Fingerprint", "Model...");
  delay(1000);

  p = finger.createModel();

  if (p == FINGERPRINT_OK) {
    Serial.println("Fingerprints matched.");
  } else if (p == FINGERPRINT_ENROLLMISMATCH) {
    Serial.println("Fingerprints did not match.");
    showMessage("Fingerprints", "Did Not Match", "Try Again");
    beepError();
    delay(2500);
    return p;
  } else {
    Serial.print("Model creation failed: ");
    Serial.println(p);
    showMessage("Model Failed", "Try Again");
    beepError();
    delay(2500);
    return p;
  }

  showMessage("Saving Finger", "ID: " + String(id));
  delay(1000);

  p = finger.storeModel(id);

  if (p == FINGERPRINT_OK) {
    Serial.println("Fingerprint stored successfully.");
    showMessage("Finger Saved", "ID: " + String(id), "Sending Server");
    beepSuccess();
    delay(2000);
    return true;
  }

  Serial.print("Store failed: ");
  Serial.println(p);
  showMessage("Store Failed", "Try Again");
  beepError();
  delay(2500);
  return p;
}

// ===================== RFID =====================
String readRFIDCard(unsigned long timeoutMs = 10000) {
  uint8_t uid[7];
  uint8_t uidLength;

  unsigned long startTime = millis();

  while (millis() - startTime < timeoutMs) {
    bool success = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, &uidLength, 500);

    if (success) {
      String uidString = "";

      for (uint8_t i = 0; i < uidLength; i++) {
        if (uid[i] < 0x10) uidString += "0";
        uidString += String(uid[i], HEX);
      }

      uidString.toUpperCase();
      return uidString;
    }

    delay(100);
  }

  return "";
}

// ===================== DEVICE CAPTURE =====================
String postToAPI(String endpoint, String postData) {
  if (WiFi.status() != WL_CONNECTED) return "";

  HTTPClient http;
  String url = api_base_url + "/" + endpoint;

  http.begin(url);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  int httpCode = http.POST(postData);
  String payload = http.getString();

  http.end();

  Serial.println(url);
  Serial.println(postData);
  Serial.println(payload);

  if (httpCode == 200) return payload;

  return "";
}

bool completeDeviceCapture(String requestId, String value) {
  String postData;
  if (device_token != "") {
    postData = "device_token=" + device_token;
  } else {
    postData = "api_key=" + api_key + "&device_code=" + device_code;
  }

  postData += "&request_id=" + requestId + "&value=" + value;

  String payload = postToAPI("complete_registration_capture.php", postData);

  if (payload.indexOf("\"success\":true") >= 0) {
    showMessage("Capture Done", value, "Saved to Server");
    beepSuccess();
    delay(3000);
    return true;
  }

  showMessage("Capture Failed", "Check Serial");
  beepError();
  delay(2500);
  return false;
}

void deviceCaptureMode() {
  if (WiFi.status() != WL_CONNECTED) {
    showMessage("No WiFi", "Cannot Capture");
    beepError();
    delay(2000);
    currentMode = MENU_MODE;
    showMainMenu();
    return;
  }

  showMessage("Device Capture", "Checking Server...");
  delay(1000);

  HTTPClient http;
  String url = api_base_url + "/get_registration_capture.php?";
  if (device_token != "") {
    url += "device_token=" + device_token;
  } else {
    url += "api_key=" + api_key + "&device_code=" + device_code;
  }

  http.begin(url);
  int httpCode = http.GET();
  String payload = http.getString();
  http.end();

  Serial.println(url);
  Serial.println(payload);

  if (!(httpCode == 200 && payload.indexOf("\"success\":true") >= 0)) {
    showMessage("No Pending", "Capture Request", "Use Admin Page");
    beepError();
    delay(3000);
    currentMode = MENU_MODE;
    showMainMenu();
    return;
  }

  String requestId = getJsonValue(payload, "request_id");
  String requestType = getJsonValue(payload, "request_type");
  String fingerIdText = getJsonValue(payload, "fingerprint_id");

  if (requestId == "") requestId = getJsonNumberValue(payload, "request_id");
  if (fingerIdText == "") fingerIdText = getJsonNumberValue(payload, "fingerprint_id");

  if (requestType == "fingerprint") {
    int fingerId = fingerIdText.toInt();

    if (fingerId <= 0 || fingerId > 127) {
      showMessage("Bad Finger ID", "Use 1 to 127", "Admin Page");
      beepError();
      delay(3000);
      currentMode = MENU_MODE;
      showMainMenu();
      return;
    }

    bool enrolled = false;
    int attempts = 0;

    while (!enrolled && attempts < 5) {
      uint8_t result = enrollFingerprint((uint8_t)fingerId);

      if (result == true) {
        completeDeviceCapture(requestId, String(fingerId));
        enrolled = true;
      } else {
        attempts++;
        showMessage("Try Again", "Place Finger", "Attempt " + String(attempts) + "/5");
        beepError();
        delay(2000);
      }
    }

    if (!enrolled) {
      showMessage("Enrollment Failed", "Try Again Later", "Use Admin Page");
      beepError();
      delay(3000);
    }
  } else if (requestType == "rfid") {
    showMessage("Capture RFID", "Tap Card");

    String uid = readRFIDCard(15000);

    if (uid != "") {
      Serial.println("RFID UID:");
      Serial.println(uid);
      showMessage("RFID Found", uid, "Sending...");
      delay(1000);
      completeDeviceCapture(requestId, uid);
    } else {
      showMessage("No Card", "Detected");
      beepError();
      delay(2500);
    }
  } else {
    showMessage("Bad Request", "Unknown Type");
    beepError();
    delay(2500);
  }

  currentMode = MENU_MODE;
  showMainMenu();
}

// ===================== ATTENDANCE MODES =====================
void fingerprintAttendanceMode() {
  fetchActiveSession();

  if (activeSessionId == 0) {
    showMessage("No Active", "Session", "Start Session");
    beepError();
    delay(1500);
    currentMode = MENU_MODE;
    showMainMenu();
    return;
  }

  if (sessionAttendanceMethod == "rfid") {
    showMessage("Fingerprint", "Not Allowed", "Use RFID Card");
    beepError();
    delay(2500);
    currentMode = MENU_MODE;
    showMainMenu();
    return;
  }

  int failCount = 0;

  while (failCount < 3) {
    showMessage("FINGERPRINT", "Place Finger");

    unsigned long startTime = millis();
    bool scanned = false;

    while (millis() - startTime < 10000) {
      int fp = getFingerprintID();

      if (fp > 0) {
        sendAttendance(String(activeSessionId), "fingerprint", String(fp));
        currentMode = MENU_MODE;
        showMainMenu();
        return;
      }

      if (fp == -2) {
        failCount++;
        scanned = true;
        showMessage("Not Recognized", "Try Again Later", String(3 - failCount) + " attempts left");
        beepError();
        delay(2000);
        break;
      }

      delay(100);
    }

    if (!scanned) {
      failCount++;
      if (failCount < 3) {
        showMessage("No Finger", "Try Again", String(3 - failCount) + " attempts left");
        beepError();
        delay(1500);
      }
    }
  }

  showMessage("Too Many Fails", "Try Again Later");
  beepError();
  delay(2000);
  currentMode = MENU_MODE;
  showMainMenu();
}

void rfidAttendanceMode() {
  fetchActiveSession();

  if (activeSessionId == 0) {
    showMessage("No Active", "Session", "Start Session");
    beepError();
    delay(1500);
    currentMode = MENU_MODE;
    showMainMenu();
    return;
  }

  if (sessionAttendanceMethod == "fingerprint") {
    showMessage("RFID", "Not Allowed", "Use Fingerprint");
    beepError();
    delay(2500);
    currentMode = MENU_MODE;
    showMainMenu();
    return;
  }

  showMessage("RFID ATTENDANCE", "Tap Card");

  String rfidUid = readRFIDCard(10000);

  if (rfidUid != "") {
    Serial.println("RFID UID:");
    Serial.println(rfidUid);
    showMessage("RFID Found", rfidUid);
    delay(1000);
    sendAttendance(String(activeSessionId), "rfid", rfidUid);
  } else {
    showMessage("No RFID", "Card Found");
    beepError();
    delay(1500);
  }

  currentMode = MENU_MODE;
  showMainMenu();
}

// ===================== DASHBOARD =====================
void dashboardMode() {
  fetchActiveSession();

  String wifi = WiFi.status() == WL_CONNECTED ? "Connected" : "Offline";
  int offline = countOfflineRecords();

  showMessage(
    "DEVICE DASHBOARD",
    "WiFi: " + wifi,
    "Session: " + String(activeSessionId),
    "Offline: " + String(offline)
  );

  delay(3000);

  showMessage(
    "DASHBOARD MENU",
    "1 Click: Capture",
    "Hold 10s: Reset",
    sessionAttendanceMethod
  );

  buttonClickCount = 0;
  bool localButtonWasDown = false;
  unsigned long localLastClick = 0;
  unsigned long localDownTime = 0;
  unsigned long startTime = millis();

  while (millis() - startTime < 8000) {
    bool state = digitalRead(BUTTON_PIN);

    if (state == LOW && !localButtonWasDown) {
      localButtonWasDown = true;
      localDownTime = millis();
    }

    if (state == LOW && localButtonWasDown) {
      if (millis() - localDownTime > 10000) {
        showMessage("Reset Settings", "Clearing...");
        delay(1000);
        clearSettings();
        ESP.restart();
      }
    }

    if (state == HIGH && localButtonWasDown) {
      localButtonWasDown = false;
      buttonClickCount++;
      localLastClick = millis();
    }

    if (buttonClickCount > 0 && millis() - localLastClick > 500) {
      if (buttonClickCount == 1) {
        buttonClickCount = 0;
        currentMode = DEVICE_CAPTURE_MODE;
        return;
      }
      buttonClickCount = 0;
    }

    delay(50);
  }

  currentMode = MENU_MODE;
  showMainMenu();
}

// ===================== BUTTON =====================
void handleButton() {
  bool currentButtonState = digitalRead(BUTTON_PIN);

  if (currentButtonState == LOW && !buttonWasDown) {
    buttonWasDown = true;
    buttonDownTime = millis();
    longPressHandled = false;
  }

  if (currentButtonState == LOW && buttonWasDown && !longPressHandled) {
    if (millis() - buttonDownTime > 3000) {
      longPressHandled = true;
      buttonClickCount = 0;
      currentMode = DASHBOARD_MODE;
      return;
    }
  }

  if (currentButtonState == HIGH && buttonWasDown) {
    buttonWasDown = false;
    if (!longPressHandled) {
      buttonClickCount++;
      lastButtonPress = millis();
    }
  }

  if (buttonClickCount > 0 && millis() - lastButtonPress > 500) {
    if (buttonClickCount == 1) {
      currentMode = FINGERPRINT_ATTENDANCE_MODE;
    } else if (buttonClickCount == 2) {
      currentMode = RFID_ATTENDANCE_MODE;
    }

    buttonClickCount = 0;
  }
}

// ===================== SETUP =====================
void setup() {
  Serial.begin(115200);
  delay(2000);

  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(BUTTON_PIN, INPUT_PULLUP);

  Wire.begin(OLED_SDA, OLED_SCL);

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("OLED not found.");
    while (true) delay(100);
  }

  showMessage("UNIVERSITY", "ATTENDANCE", "SYSTEM", "Loading...");
  delay(2000);

  LittleFS.begin(true);

  loadSettings();

  fingerSerial.begin(57600, SERIAL_8N1, FP_RX, FP_TX);
  finger.begin(57600);

  if (finger.verifyPassword()) {
    showMessage("Fingerprint", "Sensor OK");
  } else {
    showMessage("Fingerprint", "Sensor Error");
    beepError();
  }

  delay(1000);

  SPI.begin(PN532_SCK, PN532_MISO, PN532_MOSI, PN532_SS);
  SPI.setFrequency(1000000);
  nfc.begin();

  if (nfc.getFirmwareVersion()) {
    nfc.SAMConfig();
    showMessage("PN532", "RFID OK");
  } else {
    showMessage("PN532", "RFID Error");
    beepError();
  }

  delay(1000);

  if (!connectWiFi()) {
    startSetupPortal();
  }

  fetchActiveSession();
  syncSettingsFromServer();

  currentMode = MENU_MODE;
  showMainMenu();
}

// ===================== HEARTBEAT =====================
void sendHeartbeat() {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  String url = api_base_url + "/device_heartbeat.php?";
  if (device_token != "") {
    url += "device_token=" + device_token;
  } else {
    url += "api_key=" + api_key + "&device_code=" + device_code;
  }

  http.begin(url);
  int httpCode = http.GET();
  http.end();
}

// ===================== LOOP =====================
void loop() {
  if (currentMode == MENU_MODE) {
    handleButton();
  } else if (currentMode == FINGERPRINT_ATTENDANCE_MODE) {
    fingerprintAttendanceMode();
  } else if (currentMode == RFID_ATTENDANCE_MODE) {
    rfidAttendanceMode();
  } else if (currentMode == DASHBOARD_MODE) {
    dashboardMode();
  } else if (currentMode == DEVICE_CAPTURE_MODE) {
    deviceCaptureMode();
  }

  unsigned long now = millis();
  if (WiFi.status() == WL_CONNECTED && currentMode == MENU_MODE) {
    if (now - lastHeartbeat >= HEARTBEAT_INTERVAL) {
      lastHeartbeat = now;
      sendHeartbeat();
    }
    if (now - lastHeartbeat >= HEARTBEAT_INTERVAL * 6) {
      syncOfflineRecords();
    }
  }

  delay(50);
}
