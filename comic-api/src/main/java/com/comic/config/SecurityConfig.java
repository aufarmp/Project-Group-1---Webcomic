package com.comic.config;

import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.http.HttpMethod;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.config.annotation.authentication.configuration.AuthenticationConfiguration;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;

@Configuration
@EnableWebSecurity
public class SecurityConfig {

    private final JwtAuthFilter jwtAuthFilter;

    public SecurityConfig(JwtAuthFilter jwtAuthFilter) {
        this.jwtAuthFilter = jwtAuthFilter;
    }

    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder();
    }

    @Bean
    public AuthenticationManager authenticationManager(
            AuthenticationConfiguration config) throws Exception {
        return config.getAuthenticationManager();
    }

    @Bean
    public SecurityFilterChain securityFilterChain(HttpSecurity http) throws Exception {

        http
            .csrf(csrf -> csrf.disable())

            .authorizeHttpRequests(auth -> auth
                // Swagger & docs
                .requestMatchers("/swagger-ui/**", "/v3/api-docs/**",
                                 "/swagger-ui.html", "/webjars/**", "/favicon.ico").permitAll()

                // Auth
                .requestMatchers("/api/auth/**").permitAll()

                // Users
                .requestMatchers(HttpMethod.POST, "/api/users").permitAll()
                .requestMatchers(HttpMethod.PUT, "/api/users/**").permitAll()
                .requestMatchers(HttpMethod.GET, "/api/users/**").authenticated()
                .requestMatchers(HttpMethod.DELETE, "/api/users/**").authenticated()

                // Comics
                .requestMatchers(HttpMethod.GET, "/api/comics/**").permitAll()
                .requestMatchers(HttpMethod.POST, "/api/comics/**").authenticated()
                .requestMatchers(HttpMethod.PUT, "/api/comics/**").authenticated()

                // Genres
                .requestMatchers(HttpMethod.GET, "/api/genres/**").permitAll()
                .requestMatchers(HttpMethod.POST, "/api/genres/**").authenticated()

                // Default
                .anyRequest().authenticated()
            )
            .sessionManagement(sess -> sess.sessionCreationPolicy(SessionCreationPolicy.STATELESS))
            .addFilterBefore(jwtAuthFilter, UsernamePasswordAuthenticationFilter.class);

        return http.build();
    }
}
