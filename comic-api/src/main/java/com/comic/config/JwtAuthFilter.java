package com.comic.config;

import com.comic.service.MyUserDetailsService; 
import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.web.authentication.WebAuthenticationDetailsSource; 
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;

import java.io.IOException;

@Component
public class JwtAuthFilter extends OncePerRequestFilter {

    private final JwtUtil jwtUtil;
    
    private final MyUserDetailsService userDetailsService;

    public JwtAuthFilter(JwtUtil jwtUtil, MyUserDetailsService userDetailsService) {
        this.jwtUtil = jwtUtil;
        this.userDetailsService = userDetailsService;
    }

    /**
     * Skip filter untuk public endpoints & Swagger
     */
    @Override
    protected boolean shouldNotFilter(HttpServletRequest request) {
        String path = request.getRequestURI();
        String method = request.getMethod();

        if (path.startsWith("/swagger-ui") || path.startsWith("/v3/api-docs") ||
            path.equals("/swagger-ui.html") || path.startsWith("/webjars") ||
            path.equals("/favicon.ico")) return true;

        if (path.startsWith("/api/auth")) return true;

        if (path.startsWith("/api/comics") && method.equals("GET")) return true;
        if (path.startsWith("/api/genres") && method.equals("GET")) return true;

        if (path.startsWith("/api/users") && method.equals("POST")) return true;

        return false;
    }

    @Override
    protected void doFilterInternal(HttpServletRequest request,
                                    HttpServletResponse response,
                                    FilterChain filterChain)
            throws ServletException, IOException {

        String authHeader = request.getHeader("Authorization");
        if (authHeader == null || !authHeader.startsWith("Bearer ")) {

            filterChain.doFilter(request, response);
            return;
        }

        String token;
        String username;
        try {
            token = authHeader.substring(7);

            username = jwtUtil.validateToken(token); 
        } catch (Exception e) {

            response.setStatus(HttpServletResponse.SC_FORBIDDEN);
            return;
        }

        if (username != null && SecurityContextHolder.getContext().getAuthentication() == null) {
            
            UserDetails userDetails = userDetailsService.loadUserByUsername(username);

            UsernamePasswordAuthenticationToken authentication =
                    new UsernamePasswordAuthenticationToken(
                            userDetails, null, userDetails.getAuthorities());

            authentication.setDetails(new WebAuthenticationDetailsSource().buildDetails(request));

            SecurityContextHolder.getContext().setAuthentication(authentication);
        }

        filterChain.doFilter(request, response);
    }
}