package com.comic.config;

import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.security.core.userdetails.User;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;

import java.io.IOException;

@Component
public class JwtAuthFilter extends OncePerRequestFilter {

    private final JwtUtil jwtUtil;

    public JwtAuthFilter(JwtUtil jwtUtil) {
        this.jwtUtil = jwtUtil;
    }

    /**
     * Skip filter untuk public endpoints & Swagger
     */
    @Override
    protected boolean shouldNotFilter(HttpServletRequest request) {
        String path = request.getRequestURI();
        String method = request.getMethod();

        // Swagger & docs
        if (path.startsWith("/swagger-ui") || path.startsWith("/v3/api-docs") ||
            path.equals("/swagger-ui.html") || path.startsWith("/webjars") ||
            path.equals("/favicon.ico")) return true;

        // Login/register
        if (path.startsWith("/api/auth")) return true;

        // GET comics & genres
        if (path.startsWith("/api/comics") && method.equals("GET")) return true;
        if (path.startsWith("/api/genres") && method.equals("GET")) return true;

        // Register & update user
        if (path.startsWith("/api/users") && (method.equals("POST") || method.equals("PUT"))) return true;

        return false;
    }

    @Override
    protected void doFilterInternal(HttpServletRequest request,
                                    HttpServletResponse response,
                                    FilterChain filterChain)
            throws ServletException, IOException {

        String authHeader = request.getHeader("Authorization");
        if (authHeader == null || !authHeader.startsWith("Bearer ")) {
            response.setStatus(HttpServletResponse.SC_FORBIDDEN);
            return;
        }

        String token;
        String username;
        try {
            token = authHeader.substring(7);
            username = jwtUtil.validateToken(token); // validasi token
        } catch (Exception e) {
            response.setStatus(HttpServletResponse.SC_FORBIDDEN);
            return;
        }

        if (username != null && SecurityContextHolder.getContext().getAuthentication() == null) {
            UserDetails userDetails = User.builder()
                    .username(username)
                    .password("")
                    .roles("ADMIN")
                    .build();

            UsernamePasswordAuthenticationToken authentication =
                    new UsernamePasswordAuthenticationToken(
                            userDetails, null, userDetails.getAuthorities());

            SecurityContextHolder.getContext().setAuthentication(authentication);
        }

        filterChain.doFilter(request, response);
    }
}
