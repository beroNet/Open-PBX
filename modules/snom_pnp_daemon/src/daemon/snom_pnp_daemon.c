/*
 * SNOM PNP Daemon - A daemon to supply SNOM phones with provisioning information.
 *
 * Copyright (C) 2012 beroNet GmbH <support@beronet.com>
 *
 * Author: Florian Kraatz <fk@beronet.com>
 *
 */

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <signal.h>
#include <errno.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/ioctl.h>
#include <net/if.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <curl/curl.h>
#include <sqlite.h>
#include <regexp.h>

typedef struct _snom_phone_t {
	char		call_id[33];
	char		ip_addr[17];
	char		domain[256];
	char		mac_addr[13];
	char		vendor[33];
	char		type[33];
	char		version[17];
	char		tag[33];
	uint16_t	sip_port;
} snom_phone_t;

// a struct to get the data from cURLs fetching
typedef struct _snom_curl_mem_t {
	char	*memory;
	size_t	size;
} snom_curl_mem_t;

static volatile uint8_t _snom_pnp_run	= 1;

static char *_snom_pnp_ipaddr_get (const char *iface) {

	char		*ret		= NULL;

	int		sock_fd		= -1;

	struct ifreq	ifr;

	if (iface == NULL) {
		errno = EINVAL;
		return(NULL);
	}

	if ((sock_fd = socket(AF_INET, SOCK_DGRAM, 0)) < 0) {
		return(NULL);
	}

	ifr.ifr_addr.sa_family = AF_INET;
	strncpy(ifr.ifr_name, iface, (IFNAMSIZ - 1));
	ioctl(sock_fd, SIOCGIFADDR, &ifr);

	close(sock_fd);
	sock_fd = -1;

	if ((ret = calloc(17, sizeof(char))) == NULL) {
		return(NULL);
	}

	snprintf(ret, 17, "%s", inet_ntoa(((struct sockaddr_in *) &ifr.ifr_addr)->sin_addr));

	return(ret);
}

static int _snom_pnp_send_conf (const int sock_fd, struct sockaddr_in send_addr, const char *ip_addr, const snom_phone_t *phone, const size_t msg_maxlen) {

	char		msg[msg_maxlen + 1];

	if ((sock_fd < 0) || (ip_addr == NULL) || (phone == NULL)) {
		errno = EINVAL;
		return(0);
	}

	memset(msg, 0x00, sizeof(msg));

	snprintf(msg, sizeof(msg),	"SIP/2.0 200 OK\r\n"
					"Via: SIP/2.0/UDP %s:%u;rport=%u\r\n"
					"Contact: <sip:%s;transport=TCP;handler=dum>\r\n"
					"To: <sip:MAC%c3a%s@%s>;tag=%s\r\n"
					"From: <sip:MAC%c3a%s@%s>;tag=%s\r\n"
					"Call-ID: %s@%s\r\n"
					"CSeq: 1 SUBSCRIBE\r\n"
					"Expires: 0\r\n"
					"Content-Length: 0\r\n\r\n",
				phone->ip_addr, phone->sip_port, phone->sip_port,
				ip_addr,
				'%', phone->mac_addr, phone->domain, phone->tag,
				'%', phone->mac_addr, phone->domain, phone->tag,
				phone->call_id, phone->ip_addr);

	printf("Sending Confirmation-Notification: ");
	if (sendto(sock_fd, msg, strlen(msg), 0, (struct sockaddr *) &send_addr, sizeof(send_addr)) < strlen(msg)) {
		printf("Failed (%s).", strerror(errno));
		return(0);
	}
	printf("Success.\n");

	return(1);
}

static int _snom_pnp_send_prov (const int sock_fd, struct sockaddr_in send_addr, const char *ip_addr, const snom_phone_t *phone, const size_t msg_maxlen) {

	char		msg[msg_maxlen + 1],
			prov_uri[256];

	if ((sock_fd < 0) || (ip_addr == NULL) || (phone == NULL)) {
		errno = EINVAL;
		return(0);
	}

	snprintf(prov_uri, sizeof(prov_uri), "http://%s/userapp/beroPBX/phones/snom/provisioning.php?mac={mac}", ip_addr);

	memset(msg, 0x00, sizeof(msg));

	snprintf(msg, sizeof(msg),	"NOTIFY sip:%s:%u SIP/2.0\r\n"
					"Via: SIP/2.0/UDP %s:%u;rport=%u\r\n"
					"Max-Forwards: 20\r\n"
					"Contact: <sip:%s;transport=TCP;handler=dum>\r\n"
					"To: <sip:MAC%c3a%s@%s>;tag=%s\r\n"
					"From: <sip:MAC%c3a%s@%s>;tag=%s\r\n"
					"Call-ID: %s@%s\r\n"
					"CSeq: 3 NOTIFY\r\n"
					"Content-Type: application/url\r\n"
					"Subscription-State: terminated;reason=timeout\r\n"
					"Event: ua-profile;profile-type=\"device\";vendor=\"OEM\";model=\"OEM\";version=\"7.1.19\"\r\n"
					"Content-Length: %u\r\n\r\n"
					"%s",
				phone->ip_addr, phone->sip_port,
				phone->ip_addr, phone->sip_port, phone->sip_port,
				ip_addr,
				'%', phone->mac_addr, phone->domain, phone->tag,
				'%', phone->mac_addr, phone->domain, phone->tag,
				phone->call_id, phone->ip_addr,
				(unsigned int) strlen(prov_uri),
				prov_uri);

	printf("Sending Provisioning-Notification: ");
	if (sendto(sock_fd, msg, strlen(msg), 0, (struct sockaddr *) &send_addr, sizeof(send_addr)) < strlen(msg)) {
		printf("Failed (%s).\n", strerror(errno));
		return(0);
	}
	printf("Success.\n");

	return(1);
}

static int _snom_pnp_recv_conf (const int sock_fd, struct sockaddr_in rem_addr, const size_t msg_maxlen) {

	fd_set		rdfs;
	struct timeval	tv;

	int		res				= 0;

	char		msg[msg_maxlen + 1];

	socklen_t	rem_addr_len			= 0;

	memset(msg, 0x00, sizeof(msg));

	FD_ZERO(&rdfs);
	FD_SET(sock_fd, &rdfs);

	tv.tv_sec	= 5;
	tv.tv_usec	= 0;

	printf("Receiving Confirmation-Notification: ");
	if ((res = select((sock_fd + 1), &rdfs, NULL, NULL, &tv)) == -1) {
		printf("Failed (select returned '%s').\n", strerror(errno));
		return(0);
	}

	if (!FD_ISSET(sock_fd, &rdfs)) {
		printf("Failed (timeout).\n");
		return(0);
	}

	if (recvfrom(sock_fd, msg, sizeof(msg), 0, (struct sockaddr *) &rem_addr, &rem_addr_len) < 0) {
		printf("Failed (recfrom returned '%s').\n", strerror(errno));
		return(0);
	}
	printf("Success.\n");

	return(strstr(msg, "SIP/2.0 200") ? 1 : 0);
}

static int _snom_pnp_phone_add (const snom_phone_t *phone) {

	CURL		*curl			= NULL;

	FILE		*null_fp		= NULL;

	CURLcode	res;

	char		url[1024];

	snprintf(url, sizeof(url), "http://127.0.0.1/userapp/beroPBX/api/phone.php?action=add&type=%s&mac=%s&ip=%s", phone->type, phone->mac_addr, phone->ip_addr);

	printf("Adding phone to beroPBX: ");
	if (!(curl = curl_easy_init())) {
		printf("Failed (curl_init returned '%s').\n", strerror(errno));
		return(0);
	}

	if ((null_fp = fopen("/dev/null", "w")) == NULL) {
		printf("Failed (fopen returned '%s').\n", strerror(errno));

		curl_easy_cleanup(curl);

		return(0);
	}

	curl_easy_setopt(curl, CURLOPT_URL, url);
	curl_easy_setopt(curl, CURLOPT_WRITEDATA, null_fp);

	if ((res = curl_easy_perform(curl)) != CURLE_OK) {
		printf("Failed (curl_easy_perform returned '%s')\n.", curl_easy_strerror(res));

		fclose(null_fp);
		null_fp = NULL;

		curl_easy_cleanup(curl);

		return(0);
	}
	printf("Success.\n");

	fclose(null_fp);
	null_fp = NULL;

	curl_easy_cleanup(curl);

	return(1);
}

static size_t _snom_pnp_phone_chk_cb (void *cont, size_t size_i, size_t len, void *ptr) {

	size_t			size		= (size_i * len);

	snom_curl_mem_t		*data		= (snom_curl_mem_t *) ptr;

	if ((data->memory = realloc(data->memory, (data->size + size + 1))) == NULL) {
		return(0);
	}

	memcpy(&(data->memory[data->size]), cont, size);
	data->size += size;
	data->memory[data->size] = '\0';

	return(size);
}

static int _snom_pnp_phone_chk (const snom_phone_t *phone) {

	CURL			*curl			= NULL;

	int			ret			= 0;

	char			url[1024];

	snom_curl_mem_t		data;

	data.memory	= malloc(1);
	data.size	= 0;

	curl_global_init(CURL_GLOBAL_ALL);

	snprintf(url, sizeof(url), "http://127.0.0.1/userapp/beroPBX/api/phone.php?action=chk&type=%s&mac=%s&ip=%s", phone->type, phone->mac_addr, phone->ip_addr);

	printf("Check if phone is allowed to be provisioned: ");
	if (!(curl = curl_easy_init())) {
		printf("Failed (curl_init returned '%s').\n", strerror(errno));
		return(0);
	}

	curl_easy_setopt(curl, CURLOPT_URL, url);
	curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, _snom_pnp_phone_chk_cb);
	curl_easy_setopt(curl, CURLOPT_WRITEDATA, (void *) &data);
	curl_easy_perform(curl);
	curl_easy_cleanup(curl);

	ret = atoi(data.memory);

	if (data.memory) {
		free(data.memory);
		data.memory = NULL;
	}

	printf("Success (phone was %s).\n", (ret == 0) ? "rejected" : "approved");

	return(ret);
}

static int _snom_pnp_negotiate (const snom_phone_t *phone, const size_t msg_maxlen) {

	char			*ip_addr		= NULL;

	int			sock_fd			= -1;

	struct sockaddr_in	local_addr,
				remote_addr;

	if ((phone == NULL) || (msg_maxlen == 0)) {
		errno = EINVAL;
		perror("ip_addr || phone || msg_maxlen");
		return(0);
	}

	// check if phone is allowed to PNP
	if (!_snom_pnp_phone_chk(phone)) {
		return(0);
	}

	// retrieve ip-address of eth0
	if ((ip_addr = _snom_pnp_ipaddr_get("eth0")) == NULL) {
		perror("_snom_pnp_ipaddr_get()\n");
		return(0);
	}

	// setup send socket
	if ((sock_fd = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP)) < 0) {
		perror("sock_fd socket");

		free(ip_addr);
		ip_addr = NULL;

		return(0);
	}

	// setup local_addr
	memset(&local_addr, 0x00, sizeof(local_addr));
	local_addr.sin_family		= AF_INET;
	local_addr.sin_addr.s_addr	= inet_addr(ip_addr);
	local_addr.sin_port		= htons(35060);

	if (bind(sock_fd, (struct sockaddr *) &local_addr, sizeof(local_addr)) < 0) {
		perror("sock_fd bind");

		free(ip_addr);
		ip_addr = NULL;

		close(sock_fd);
		sock_fd = -1;

		return(0);
	}

	// setup remote_addr
	memset(&remote_addr, 0x00, sizeof(remote_addr));
	remote_addr.sin_family		= AF_INET;
	remote_addr.sin_addr.s_addr	= inet_addr(phone->ip_addr);
	remote_addr.sin_port		= htons(phone->sip_port);

	// send confirmation to phone
	if (!_snom_pnp_send_conf(sock_fd, remote_addr, ip_addr, phone, msg_maxlen)) {

		free(ip_addr);
		ip_addr = NULL;

		close(sock_fd);
		sock_fd = -1;

		return(0);
	}

	// create / update phone in database
	if (!_snom_pnp_phone_add(phone)) {

		free(ip_addr);
		ip_addr = NULL;

		close(sock_fd);
		sock_fd = -1;

		return(0);
	}

	// send provisioning information to phone
	if (!_snom_pnp_send_prov(sock_fd, remote_addr, ip_addr, phone, msg_maxlen)) {

		free(ip_addr);
		ip_addr = NULL;

		close(sock_fd);
		sock_fd = -1;

		return(0);
	}

	// receive confirmation from phone
	if (!_snom_pnp_recv_conf(sock_fd, remote_addr, msg_maxlen)) {

		free(ip_addr);
		ip_addr = NULL;

		close(sock_fd);
		sock_fd = -1;

		return(0);
	}

	free(ip_addr);
	ip_addr = NULL;

	close(sock_fd);
	sock_fd = -1;

	return(1);
}

static snom_phone_t *_snom_pnp_parse (const char *msg) {

	snom_phone_t	*ret		= NULL;

	char		*line		= NULL,
			*ptr		= NULL;

	char		tmp[128];

	if (msg == NULL) {
		perror("msg == NULL");
		errno = EINVAL;
		return(NULL);
	}

	if ((ret = malloc(sizeof(snom_phone_t))) == NULL) {
		perror("malloc");
		return(NULL);
	}
	memset(ret, 0x00, sizeof(snom_phone_t));

	// get SUBSCRIBE line
	if ((line = re_newstr(msg, "SUBSCRIBE.+", (REG_EXTENDED | REG_NEWLINE))) == NULL) {
		free(ret);
		ret = NULL;

		return(NULL);
	}

	// get mac_addr
	if ((ptr = re_newstr(line, "MAC\%3a[0-9a-fA-F]{12}", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(ret->mac_addr, 13, "%s", ptr + strlen("MAC%3a"));
		free(ptr);
		ptr = NULL;
	}

	// get domain
	if ((ptr = re_newstr(line, "@[-0-9a-zA-Z\\_\\.]*", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(ret->domain, 256, "%s", ptr + strlen("@"));
		free(ptr);
		ptr = NULL;
	}

	free(line);
	line = NULL;

	// get From line
	if ((line = re_newstr(msg, "From:.+", (REG_EXTENDED | REG_NEWLINE))) == NULL) {
		free(ret);
		ret = NULL;

		return(NULL);
	}

	if ((ptr = re_newstr(line, "tag=[0-9]*", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(ret->tag, 32, "%s", ptr + strlen("tag="));
		free(ptr);
		ptr = NULL;
	}

	free(line);
	line = NULL;

	// get Call-ID line
	if ((line = re_newstr(msg, "Call-ID:.+", (REG_EXTENDED | REG_NEWLINE))) == NULL) {
		free(ret);
		ret = NULL;

		return(NULL);
	}

	// get call-id
	if ((ptr = re_newstr(line, " [0-9]*", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(ret->call_id, 32, "%s", ptr + strlen(" "));
		free(ptr);
		ptr = NULL;
	}

	// get ip_addr
	if ((ptr = re_newstr(line, "[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(ret->ip_addr, 16, "%s", ptr);
		free(ptr);
		ptr = NULL;
	}

	free(line);
	line = NULL;

	// get Event line
	if ((line = re_newstr(msg, "Event:.+", (REG_EXTENDED | REG_NEWLINE))) == NULL) {
		free(ret);
		ret = NULL;

		return(NULL);
	}

	// get vendor
	if ((ptr = re_newstr(line, "vendor=\"[A-Za-z0-9]*", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(ret->vendor, 32, "%s", ptr + strlen("vendor=\""));
		free(ptr);
		ptr = NULL;
	}

	// get model
	if ((ptr = re_newstr(line, "model=\"[A-Za-z0-9]*", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(ret->type, 32, "%s", ptr + strlen("model=\""));
		free(ptr);
		ptr = NULL;
	}

	// get version
	if ((ptr = re_newstr(line, "version=\"[0-9\\.]*", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(ret->version, 16, "%s", ptr + strlen("version=\""));
		free(ptr);
		ptr = NULL;
	}

	free(line);
	line = NULL;

	// get Contact line
	if ((line = re_newstr(msg, "Contact:.+", (REG_EXTENDED | REG_NEWLINE))) == NULL) {
		free(ret);
		ret = NULL;

		return(NULL);
	}

	// get sip-port
	if ((ptr = re_newstr(line, ":[0-9]*>", (REG_EXTENDED | REG_NEWLINE))) != NULL) {
		snprintf(tmp, 6, "%s", ptr + strlen(":"));
		if (tmp[strlen(tmp) - 1] == '>') {
			tmp[strlen(tmp) - 1] = '\0';
		}
		ret->sip_port = atoi(tmp);

		free(ptr);
		ptr = NULL;
	}

	free(line);
	line = NULL;

	return(ret);
}

static int _snom_pnp_mcast_init (const char *mc_addr, const uint16_t mc_port) {

	int			mcast_sock		= -1;

	uint32_t		tmp			= 1;

	struct sockaddr_in	mcast_addr;

	struct ip_mreq		mcast_mreq;


	// setup multicast group
	memset(&mcast_addr, 0x00, sizeof(mcast_addr));
	mcast_addr.sin_family		= AF_INET;
	mcast_addr.sin_addr.s_addr	= inet_addr(mc_addr);
	mcast_addr.sin_port		= htons(mc_port);

	// setup mcast recv socket
	if ((mcast_sock = socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP)) < 0) {
		return(-errno);
	}

	if (setsockopt(mcast_sock, SOL_SOCKET, SO_REUSEADDR, &tmp, sizeof(tmp)) < 0) {
		close(mcast_sock);
		return(-errno);
	}

	if (bind(mcast_sock, (struct sockaddr *) &mcast_addr, sizeof(mcast_addr)) < 0) {
		close(mcast_sock);
		return(-errno);
	}

	mcast_mreq.imr_multiaddr.s_addr = inet_addr(mc_addr);
	mcast_mreq.imr_interface.s_addr = htonl(INADDR_ANY);

	if (setsockopt(mcast_sock, IPPROTO_IP, IP_ADD_MEMBERSHIP,(uint8_t *) &mcast_mreq, sizeof(mcast_mreq)) < 0) {
		close(mcast_sock);
		return(-errno);
	}

	return(mcast_sock);
}

static int _snom_pnp_mcast_data_avail (const int sock_fd, const uint32_t timeout) {

	fd_set		rdfs;
	struct timeval	tv;

	int		res		= 0;

	FD_ZERO(&rdfs);
	FD_SET(sock_fd, &rdfs);

	tv.tv_sec	= timeout;
	tv.tv_usec	= 0;

	if (((res = select((sock_fd + 1), &rdfs, NULL, NULL, &tv)) == -1) || (res == 0)) {
		return(0);
	}

	return(1);
}

static void _snom_pnp_sig_hndl (int sig_n) {

	_snom_pnp_run = 0;
}

int main (int argc, char **argv) {

	const char		mc_addr[]		= "224.0.1.75";

	const uint16_t		mc_port			= 5060;

	const size_t		msg_maxlen		= 1024;


	int			mcast_sock		= -1,
				n			= 0,
				ret			= 0;

	snom_phone_t		*phone			= NULL;

	char			msg[msg_maxlen + 1];

	struct sockaddr_in	remote_addr;

	socklen_t		remote_addr_len		= 0;

	if ((mcast_sock = _snom_pnp_mcast_init(mc_addr, mc_port)) < 0) {
		fprintf(stderr, "Could not init Multicast for address %s:%u (error %s)\n", mc_addr, mc_port, strerror(errno));
		return(errno);
	}

	signal(SIGINT, _snom_pnp_sig_hndl);
	signal(SIGTERM, _snom_pnp_sig_hndl);

	while (_snom_pnp_run) {

		// only receive if data is available
		if (!_snom_pnp_mcast_data_avail(mcast_sock, 5)) {
			continue;
		}

		memset(msg, 0x00, sizeof(msg));
		remote_addr_len = sizeof(remote_addr);

		// receive data
		printf("Incoming Subscription-Request: ");
		if ((n = recvfrom(mcast_sock, msg, (sizeof(msg) - 1), 0, (struct sockaddr*) &remote_addr, &remote_addr_len)) < 0) {
			printf("Failed (%s).\n", strerror(errno));
			ret = errno;
			break;
		}
		printf("Success (from %s).\n", inet_ntoa(remote_addr.sin_addr));

		// try to parse data
		printf("Parsing Request: ");
		if ((phone = _snom_pnp_parse(msg)) == NULL) {
			printf("Failed (%s).\n", strerror(errno));
			continue;
		}
		printf("Success (%s is a %s).\n", phone->ip_addr, phone->type);

		// tell phone where the provisioning-information lies
		_snom_pnp_negotiate(phone, msg_maxlen);

		free(phone);
		phone = NULL;
	}

	close(mcast_sock);
	mcast_sock = -1;

	return(ret);
}
