# RELEASE_SIGNING.md — Signature release Android — StatEduc Mobile

## Pourquoi ce fichier existe

Sans signature release, l'APK est signé avec la clé de **débogage Android**
(`debug.keystore`). Google PlayProtect reconnaît cette clé comme non-fiable et
**bloque l'installation** sur tous les appareils Android avec un avertissement
de sécurité. L'utilisateur doit désactiver PlayProtect pour continuer.

**Avec une clé release propre**, PlayProtect laisse l'APK s'installer sans
avertissement.

---

## Fichiers impliqués

| Fichier | Contenu | Versionné ? |
|---------|---------|-------------|
| `android/app/stateduc_release.jks` | Keystore PKCS12 — clé privée RSA 2048 bits | ❌ **JAMAIS** |
| `android/key.properties` | Mot de passe + alias de la keystore | ❌ **JAMAIS** |
| `android/app/build.gradle` | Lit `key.properties`, configure `signingConfigs.release` | ✅ oui |
| `.gitignore` | Exclut `*.jks`, `*.keystore`, `key.properties` | ✅ oui |

> ⚠️ **Ne jamais committer `stateduc_release.jks` ou `key.properties` dans Git.**
> Si la clé privée est exposée, n'importe qui peut publier une mise à jour
> malveillante de l'application sous votre identité.

---

## Informations de la keystore (générée le 2026-07-16)

```
Fichier    : android/app/stateduc_release.jks
Alias      : stateduc_key
Algorithme : RSA 2048 bits / SHA384withRSA
Validité   : 10 000 jours (jusqu'au ~01/12/2053)
CN         : StatEduc Mobile
OU         : UNESCO IIEP
O          : Ministere Education Burundi
L          : Bujumbura
ST         : Bujumbura Mairie
C          : BI

Empreinte SHA256 :
  35:39:D8:F4:BA:FD:B2:13:91:D4:B4:8E:56:FA:E6:84:
  95:3E:7C:5E:46:A4:8C:99:63:5B:E1:AC:7D:72:B7:22
```

> 🔐 **Conservez `stateduc_release.jks` et les mots de passe dans un endroit
> sûr et séparé** (gestionnaire de mots de passe, coffre chiffré).
> Si vous perdez la keystore, vous ne pourrez plus publier de mises à jour
> de l'application — vous devrez republier sous un nouvel identifiant.

---

## Procédure de build APK release (sur votre machine)

### Prérequis

1. Avoir Flutter installé et configuré
2. Avoir `android/app/stateduc_release.jks` sur votre machine
3. Avoir `android/key.properties` configuré (voir modèle ci-dessous)

### 1. Vérifier / créer `android/key.properties`

```properties
storePassword=StatEduc@2026!Release
keyPassword=StatEduc@2026!Release
keyAlias=stateduc_key
storeFile=stateduc_release.jks
```

> `storeFile` est un chemin **relatif à `android/app/`**.

### 2. Builder l'APK release

```bash
cd stateduc_flutter/
flutter build apk --release
```

L'APK signé se trouve dans :
```
build/app/outputs/flutter-apk/app-release.apk
```

### 3. Vérifier la signature

```bash
# Vérifier que l'APK est signé avec la clé release (pas debug)
keytool -printcert -jarfile build/app/outputs/flutter-apk/app-release.apk
```

Vous devez voir le CN `StatEduc Mobile` et l'empreinte SHA256 ci-dessus.
Si vous voyez `CN=Android Debug`, la configuration n'est pas appliquée.

### 4. Installer sur un appareil Android

```bash
# Via adb (câble USB)
adb install build/app/outputs/flutter-apk/app-release.apk

# Ou transférer l'APK sur l'appareil et l'ouvrir
```

L'installation se fait **sans avertissement PlayProtect** ✅

---

## Builder un App Bundle (Google Play Store)

Si vous souhaitez publier sur le Play Store :

```bash
flutter build appbundle --release
```

L'AAB se trouve dans :
```
build/app/outputs/bundle/release/app-release.aab
```

---

## Recréer la keystore (si perdue)

Si `stateduc_release.jks` est perdu, vous devrez créer une **nouvelle** keystore.
**Attention** : les APK signés avec une nouvelle clé sont incompatibles avec les
installations existantes (l'utilisateur devra désinstaller puis réinstaller).

```bash
keytool -genkey -v \
  -keystore android/app/stateduc_release.jks \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000 \
  -alias stateduc_key \
  -storepass "NOUVEAU_MOT_DE_PASSE" \
  -keypass  "NOUVEAU_MOT_DE_PASSE" \
  -dname "CN=StatEduc Mobile, OU=UNESCO IIEP, O=Ministere Education Burundi, L=Bujumbura, ST=Bujumbura Mairie, C=BI"
```

Puis mettre à jour `android/key.properties` avec les nouveaux mots de passe.

---

## Checklist avant chaque build release

- [ ] `android/app/stateduc_release.jks` est présent sur la machine
- [ ] `android/key.properties` est configuré avec les bons mots de passe
- [ ] `flutter doctor` ne signale aucun problème
- [ ] `flutter build apk --release` se termine sans erreur
- [ ] L'APK généré est vérifié avec `keytool -printcert`
- [ ] L'APK s'installe sans avertissement PlayProtect sur un appareil test
