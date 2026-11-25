# VanService Driver Native Android

This is the **replacement native Kotlin Android app** for VanService driver tracking. It uses the existing Laravel mobile API and has no Expo, React Native, Metro, Node.js, or JavaScript bundle dependency at runtime.

## Included features

The app allows a driver to sign in with the existing Laravel driver account, share precise location only while the screen is visible, update the administrator’s live map, load the assigned trip, report van takeoff, and trigger the existing server-verified destination-arrival geofence.

## Open in Android Studio

Open the **`vanservice-driver-native`** folder itself in Android Studio. This is a complete Android project; do not open an `android` subfolder.

Use **JDK 17** and install Android SDK Platform 35 when Android Studio requests it. Gradle downloads the required Kotlin and Android libraries automatically.

## Run on a USB-connected phone or tablet

Start Laravel first on the computer:

```bat
cd C:\xampp\htdocs\van_service_laravel
C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
```

With USB debugging enabled on the phone/tablet, run the following in a second terminal:

```bat
adb reverse tcp:8000 tcp:8000
```

Then select the device in Android Studio and click **Run**. The native app automatically tests `http://127.0.0.1:8000` through this USB tunnel. There is no Metro server to start.

## Use over Wi-Fi

The app automatically searches the current Wi-Fi network for the approved VanService health endpoint. No computer IPv4 address needs to be entered or compiled into the app. The scan is bounded to the connected local `/24` network and accepts only a server that returns the expected VanService service identity.

Start Laravel on the computer and allow port `8000` through Windows Firewall:

```bat
C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
```

Connect the phone/tablet and computer to the same Wi-Fi, then open the app. If discovery cannot verify the server, use **Try Again** after confirming both devices are on the same network. Use an HTTPS address before distributing a production build outside the local network.

## Installable APK

Android Studio can create an independent APK using **Build → Generate Signed Bundle / APK → APK**. The debug APK works for local testing, while a signed release APK is recommended for normal driver use.

## Laravel requirements

Use the latest Laravel update package and run:

```bat
php artisan migrate
php artisan optimize:clear
```

The driver must have a van assigned by an administrator. An active trip must have a saved destination coordinate for the automatic arrival alert.
