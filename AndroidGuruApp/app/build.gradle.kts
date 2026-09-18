// ========================================================
// build.gradle.kts (App Module) — WebView Edition
// SMK Al-Farizi — Aplikasi Android Guru (WebView)
// ========================================================
plugins {
    alias(libs.plugins.android.application)
    alias(libs.plugins.kotlin.android)
}

android {
    namespace = "com.smkalfarizi.guru"
    compileSdk = 35

    defaultConfig {
        applicationId = "com.smkalfarizi.guru"
        minSdk = 26          // Android 8.0+
        targetSdk = 35
        versionCode = 1
        versionName = "1.0.0"

        testInstrumentationRunner = "androidx.test.runner.AndroidJUnitRunner"

        // URL Web Server (ganti ke hosting/VPS saat production)
        buildConfigField("String", "WEB_BASE_URL", "\"https://absensismkalfarizi.my.id\"")
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
            buildConfigField("String", "WEB_BASE_URL", "\"https://absensismkalfarizi.my.id\"")
        }
        debug {
            isDebuggable = true
            buildConfigField("String", "WEB_BASE_URL", "\"https://absensismkalfarizi.my.id\"")
        }
    }

    buildFeatures {
        viewBinding = true
        buildConfig = true
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_11
        targetCompatibility = JavaVersion.VERSION_11
    }

    kotlinOptions {
        jvmTarget = "11"
    }
}

dependencies {
    implementation(libs.androidx.core.ktx)
    implementation(libs.androidx.appcompat)
    implementation(libs.material)
    implementation(libs.androidx.constraintlayout)

    // Lifecycle
    implementation(libs.androidx.lifecycle.runtime.ktx)

    // GPS (FusedLocationProvider untuk jembatan JS → GPS)
    implementation(libs.play.services.location)
}
