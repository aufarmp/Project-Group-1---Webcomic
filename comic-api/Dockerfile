# Tahap 1: Build (Menggunakan Maven untuk membuat file .jar)
FROM maven:3.9.6-eclipse-temurin-21 AS build
WORKDIR /app
COPY pom.xml .
COPY src ./src
# Build aplikasi tanpa menjalankan test (biar cepat)
RUN mvn clean package -DskipTests

# Tahap 2: Run (Menjalankan aplikasi hasil build)
FROM eclipse-temurin:21-jre-alpine
WORKDIR /app
# Copy file .jar dari tahap 1 ke tahap 2
COPY --from=build /app/target/*.jar app.jar
EXPOSE 8080
ENTRYPOINT ["java", "-jar", "app.jar"]