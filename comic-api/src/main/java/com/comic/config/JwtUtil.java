package com.comic.config;

import com.comic.service.UserDetailsImpl; 
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.io.Decoders;
import io.jsonwebtoken.security.Keys;
import org.springframework.security.core.Authentication;
import org.springframework.security.core.GrantedAuthority;
import org.springframework.stereotype.Component;

import javax.crypto.SecretKey;
import java.util.Date;
import java.util.List;
import java.util.stream.Collectors; 

@Component
public class JwtUtil {

    private static final String SECRET =
            "uA6ZlDzFf6FvO6YvWZ9yWv7mCS3uT4pQYJx2e7v8xWk=";

    private final SecretKey key =
            Keys.hmacShaKeyFor(Decoders.BASE64.decode(SECRET));

    public String generateJwtToken(Authentication authentication) {

        UserDetailsImpl userPrincipal = (UserDetailsImpl) authentication.getPrincipal();
        
        List<String> roles = userPrincipal.getAuthorities().stream()
                .map(GrantedAuthority::getAuthority)
                .collect(Collectors.toList());

        return generateToken(userPrincipal.getUsername(), roles);
    }

    public String generateToken(String username, List<String> roles) {
        long now = System.currentTimeMillis();
        return Jwts.builder()
                .subject(username)
                .claim("roles", roles)
                .issuedAt(new Date(now))
                .expiration(new Date(now + 1000 * 60 * 60)) // 1 jam
                .signWith(key)
                .compact();
    }

    public String generateToken(String username) {

        long now = System.currentTimeMillis();
        return Jwts.builder()
                .subject(username)
                .issuedAt(new Date(now))
                .expiration(new Date(now + 1000 * 60 * 60))
                .signWith(key)
                .compact();
    }

    public String validateToken(String token) {
        return Jwts.parser()
                .verifyWith(key)
                .build()
                .parseSignedClaims(token)
                .getPayload()
                .getSubject();
    }
}