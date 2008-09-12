#!/usr/bin/perl -w
use strict;
my ($plain) = @ARGV;
my $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
my $salt;
for (1..2) { $salt .= substr $itoa64, rand(length($itoa64)), 1; }
my $password = crypt($plain, $salt);
print "$password";
