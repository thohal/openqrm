// openqrm-exec-port-monitor.c
// this small daemon listens on the openQRM-exec port
// and waits for administrative commands
// If a command it received it prints out the sender 
// ip-address + the command and handles over the control
// the the openqrm-execd which then runs the command


#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/socket.h>
#include <sys/types.h>
#include <sys/select.h>
#include <netinet/in.h>

void usage() {
	printf("Usage :	openqrm-exec-port-monitor <ip-address> <port-number>");
	exit(2);
}


int main(int argc, char** argv) {
	struct sockaddr_in address;
	int sock;
	int option = 1;
	
	if (argc < 3) {
		usage();
	}
	if ((sock = socket(AF_INET, SOCK_STREAM, 0)) == -1) {
		perror("ERROR: Could not create socket");
		exit(1);
	}
	memset(&address, 0, sizeof(address));
    address.sin_family = AF_INET;
	if (!inet_aton(argv[1], (struct in_addr *)&address.sin_addr.s_addr)) {
		perror("ERROR: Given Ip-address is not valid");
		close(sock);
		exit(1);
	}
	address.sin_port = htons(atoi(argv[2]));
	setsockopt(sock, SOL_SOCKET, SO_REUSEADDR, (char *)&option, (socklen_t)sizeof(option));
	if(bind(sock, (struct sockaddr *) &address, sizeof( struct sockaddr_in)) == -1) {
		perror("ERROR: Could not bind the socket");
		close(sock);
		exit(1);
	}
	listen(sock, 5);
	// main loop
	while(1) {
		fd_set fdset;
		FD_ZERO(&fdset);
        FD_SET(sock, &fdset);
		switch (select(sock + 1, &fdset, NULL, NULL, NULL /* &tv */)) {
			case -1:                                // error
				perror("ERROR: Error while reading from socket");
				close(sock);
				exit(1);
			case 0:
				continue;
			case 1:
				break;
		}

		int fd; 
		struct sockaddr_in peer;
		socklen_t len = sizeof(peer);
		char buf[1024];
		ssize_t msgsize;
		if ((fd = accept(sock,(struct sockaddr *)&peer, &len)) == -1) {
			perror("ERROR: Error while accepting connection");
			fflush(stdout);
			fflush(stderr);
		}
		if ((msgsize = read(fd, buf, sizeof(buf) - 1)) == -1) {
			perror("ERROR: Error while reading the message");
			close(fd);
			fflush(stdout);
			fflush(stderr);
		}
		if (buf[msgsize - 1] == '\n') {
			msgsize--;
		}
		buf[msgsize] = '\0';
		printf("%s:%s\n",inet_ntoa(peer.sin_addr.s_addr), buf);
		close(fd);
		fflush(stdout);
		fflush(stderr);
	}
}


