package com.comic.service;

import org.springframework.security.core.userdetails.User;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.core.userdetails.UsernameNotFoundException;
import org.springframework.stereotype.Service;

@Service
public class MyUserDetailsService implements UserDetailsService {

    @Override
    public UserDetails loadUserByUsername(String username) throws UsernameNotFoundException {
        if (!username.equals("admin")) {
            throw new UsernameNotFoundException("User not found");
        }

        // Hash baru ini PASTI untuk password: "123"
        return User.builder()
                .username("admin")
                .password("$2a$10$2Mbusf71Q.jSJjzezb53y.uyCmtfyRPFk5FdV.kw4xv520FqXvBHK") 
                .roles("ADMIN")
                .build();
    }
}