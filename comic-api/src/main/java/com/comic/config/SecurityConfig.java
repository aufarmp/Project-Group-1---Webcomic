package com.comic.config;

import com.comic.service.MyUserDetailsService; // Import service Anda
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.http.HttpMethod;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.dao.DaoAuthenticationProvider; // Import Provider
import org.springframework.security.config.Customizer; // Import Customizer
import org.springframework.security.config.annotation.authentication.configuration.AuthenticationConfiguration;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;
import org.springframework.web.cors.CorsConfiguration; // Import CORS
import org.springframework.web.cors.CorsConfigurationSource;
import org.springframework.web.cors.UrlBasedCorsConfigurationSource;

import java.util.Arrays;
import java.util.List;

@Configuration
@EnableWebSecurity
public class SecurityConfig {

    private final JwtAuthFilter jwtAuthFilter;
    private final MyUserDetailsService myUserDetailsService;

    public SecurityConfig(JwtAuthFilter jwtAuthFilter, MyUserDetailsService myUserDetailsService) {
        this.jwtAuthFilter = jwtAuthFilter;
        this.myUserDetailsService = myUserDetailsService;
    }

    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder();
    }

    @Bean
    public DaoAuthenticationProvider authenticationProvider() {
        DaoAuthenticationProvider authProvider = new DaoAuthenticationProvider();
        authProvider.setUserDetailsService(myUserDetailsService);
        authProvider.setPasswordEncoder(passwordEncoder());
        return authProvider;
    }

    @Bean
    public AuthenticationManager authenticationManager(AuthenticationConfiguration config) throws Exception {
        return config.getAuthenticationManager();
    }

    @Bean
    public SecurityFilterChain securityFilterChain(HttpSecurity http) throws Exception {
        http
            .csrf(csrf -> csrf.disable())
            .cors(Customizer.withDefaults()) 
            
            .sessionManagement(sess -> sess.sessionCreationPolicy(SessionCreationPolicy.STATELESS))
            
            .authorizeHttpRequests(auth -> auth
                // Swagger & docs
                .requestMatchers("/swagger-ui/**", "/v3/api-docs/**", "/swagger-ui.html", "/webjars/**", "/favicon.ico").permitAll()

                // Auth
                .requestMatchers("/api/auth/**").permitAll()

                // Users
                .requestMatchers(HttpMethod.POST, "/api/users").permitAll() // Register Public
                .requestMatchers(HttpMethod.PUT, "/api/users/**").authenticated()
                .requestMatchers(HttpMethod.GET, "/api/users/**").authenticated()
                .requestMatchers(HttpMethod.DELETE, "/api/users/**").hasRole("ADMIN")

                // Comics
                .requestMatchers(HttpMethod.GET, "/api/comics/**").permitAll()
                .requestMatchers(HttpMethod.POST, "/api/comics/**").authenticated()
                .requestMatchers(HttpMethod.PUT, "/api/comics/**").authenticated()

                // Genres
                .requestMatchers(HttpMethod.GET, "/api/genres/**").permitAll()
                .requestMatchers(HttpMethod.POST, "/api/genres/**").hasRole("ADMIN")

                .anyRequest().authenticated()
            );

        // Set Provider
        http.authenticationProvider(authenticationProvider());

        // Add Filter
        http.addFilterBefore(jwtAuthFilter, UsernamePasswordAuthenticationFilter.class);

        return http.build();
    }

    @Bean
    CorsConfigurationSource corsConfigurationSource() {
        CorsConfiguration configuration = new CorsConfiguration();
        configuration.setAllowedOrigins(List.of("*"));
        configuration.setAllowedMethods(Arrays.asList("GET", "POST", "PUT", "DELETE", "OPTIONS"));
        configuration.setAllowedHeaders(Arrays.asList("Authorization", "Content-Type"));
        configuration.setExposedHeaders(List.of("Authorization"));
        
        UrlBasedCorsConfigurationSource source = new UrlBasedCorsConfigurationSource();
        source.registerCorsConfiguration("/**", configuration);
        return source;
    }
}