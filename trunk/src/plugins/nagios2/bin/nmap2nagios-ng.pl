#!/usr/bin/perl -X
#
# Docs are at the bottom below the =head1.
# You could just type perldoc nmap2nagios.pl;
#
#~~~~~~~
#Copyright (c) 2001-2002 Todd A. Green.
#Copyright (c) 2008 Michel Sigloch
#All rights reserved.  This program is free software; 
#you can redistribute it and/or modify it under the same terms as Perl itself.
#~~~~~~~

use strict;
no strict("refs");
use warnings;
no warnings("deprecated");

use Data::Dumper; 
$Data::Dumper::Indent = 1;
$Data::Dumper::Maxdepth = 3;
 
use File::Path;
use File::Basename;
use Getopt::Std;
use IO::File;
use XML::Simple;


my $loopcount_service = 0;
my $loopcount_host = 0;
 
my $data_ref = {};
 
($data_ref->{'Program'},
 $data_ref->{'Path'}) = fileparse($0);
 
$data_ref->{'Version'} = '0.1.3';

$data_ref->{'nmap2nagios_args'} = join(' ', $data_ref->{'Program'}, @ARGV);

my $options_ref = {};
getopts('hvVic:r:o:zd', $options_ref);
$options_ref->{'configuration_file'}      = $options_ref->{'c'};
$options_ref->{'results_file'}            = $options_ref->{'r'};
$options_ref->{'output_file'}             = $options_ref->{'o'};
$options_ref->{'ignore_unknown_services'} = $options_ref->{'i'};
$options_ref->{'VERBOSE'}                 = $options_ref->{'V'};
$options_ref->{'Verbose'}                 = $options_ref->{'v'} || $options_ref->{'V'};

 
if (defined $options_ref->{'h'} || !$options_ref->{'r'} || !$options_ref->{'o'}){
  print $data_ref->{'Program'}, ": -v -i -z||d -r {'nmap_results_file'} -o {'output_file'}\n" .
        "  -i Ignore Unknown Services\n" .
        "  -v Verbose\n" .
        "  -V Serious Verbose\n" .
		"  -z Nagios Version 2.x\n" .
		"  -d Nagios Version 3.x\n" .
		"  -h This screen\n";
  exit;
}

#Nur zur sicherheit
if ($options_ref->{'output_file'} eq 'hosts.cfg') {
  print "I'm sorry.  It's not that I don't trust you, but I'm not going to possibly overwrite your hosts.cfg\n";
  exit;
}

if ($options_ref->{'z'})
{
print("\n\tYou have choosen to generate Nagios 2.x - Config\n");
$options_ref->{'configuration_file'} = $data_ref->{'Path'} . '/nmap2nagios-ng.conf' if (!$options_ref->{'configuration_file'});
$options_ref->{'configuration_file'} = './' . $options_ref->{'configuration_file'} if ($options_ref->{'configuration_file'} !~ /^(\/|\.\/)/);
}
elsif ($options_ref->{'d'})
{
print("\n\tYou have choosen to generate Nagios 3.x - Config\n");
$options_ref->{'configuration_file'} = $data_ref->{'Path'} . '/nmap2nagios-ng_3x.conf' if (!$options_ref->{'configuration_file'});
$options_ref->{'configuration_file'} = './' . $options_ref->{'configuration_file'} if ($options_ref->{'configuration_file'} !~ /^(\/|\.\/)/);
}
else
{
print("\n\tYou have not choosen a Nagios-Version! Using default (2.x) \n");
$options_ref->{'configuration_file'} = $data_ref->{'Path'} . '/nmap2nagios-ng.conf' if (!$options_ref->{'configuration_file'});
$options_ref->{'configuration_file'} = './' . $options_ref->{'configuration_file'} if ($options_ref->{'configuration_file'} !~ /^(\/|\.\/)/);
}

my $config_ref = load_configuration($options_ref);

if ($config_ref) {
  my $results_ref = load_results($options_ref);
  if ($results_ref) {
    my $hosts_ref = process_results($options_ref, $results_ref);
    if ($hosts_ref) {
      output_nagios_config($options_ref, $config_ref, $hosts_ref);
    }
  }
}

sub process_results {
  my $options_ref = shift;
  my $results_ref = shift;
  my $hosts_ref = shift;

  $hosts_ref = {} if (!defined $hosts_ref);

  my $run_info_ref = split_timestamp();

  # nmap stuff uebernehmen
  $run_info_ref->{'nmap2nagios_version'} = 'v' . $data_ref->{'Version'};
  $run_info_ref->{'nmap2nagios_args'} = $data_ref->{'nmap2nagios_args'};
  $run_info_ref->{'nmap_args'} = $results_ref->{'args'};
  $run_info_ref->{'nmap_version'} = $results_ref->{'version'};
  $run_info_ref->{'nmap_start_timestamp'} = $results_ref->{'start'};
  my $start_time_ref = split_timestamp($run_info_ref->{'nmap_start_timestamp'});
  $run_info_ref->{'nmap_start'} = $start_time_ref->{'date_time'};
  $run_info_ref->{'nmap_finish_timestamp'} = $results_ref->{'runstats'}->{'finished'}->{'time'};
  my $finish_time_ref = split_timestamp($run_info_ref->{'nmap_finish_timestamp'});
  $run_info_ref->{'nmap_finish'} = $finish_time_ref->{'date_time'};
  $run_info_ref->{'nmap_duration'} = seconds2minutes($run_info_ref->{'nmap_finish_timestamp'} - $run_info_ref->{'nmap_start_timestamp'});
  $run_info_ref->{'nmap_hosts_up'} = $results_ref->{'runstats'}->{'hosts'}->{'up'};
  $run_info_ref->{'nmap_hosts_down'} = $results_ref->{'runstats'}->{'hosts'}->{'down'};
  $run_info_ref->{'nmap_hosts_total'} = $results_ref->{'runstats'}->{'hosts'}->{'total'};
  $hosts_ref->{'run_info'} = $run_info_ref;

  # nur ein host -> kein array
  $results_ref->{'host'} = [ $results_ref->{'host'} ] if (ref($results_ref->{'host'}) ne 'ARRAY');
  # host/service informationen einlesen
  foreach my $nmap_host_ref (@ { $results_ref->{'host'} }) {
    next if ($nmap_host_ref->{'status'}->{'state'} ne 'up');
    # broadcast, netz und macaddressen verwerfen
    next if ($nmap_host_ref->{'address'}->{'addr'} =~ /\.0$/);
    next if ($nmap_host_ref->{'address'}->{'addr'} =~ /\.255$/);
    next if ($nmap_host_ref->{'address'}->{'addr'} =~ /^([0-9A-F]{2}([:-]|$)){6}$/i);

    my $host_ref = {};
    if (ref($nmap_host_ref->{'address'}) ne 'ARRAY')
    {
    $host_ref->{'address'} = $nmap_host_ref->{'address'}->{'addr'};
    }
    else
    {
    $host_ref->{'address'} = $nmap_host_ref->{'address'}->[0]->{'addr'};
    }
    $host_ref->{'host_name'} = $nmap_host_ref->{'hostnames'}->{'hostname'}->{'name'};
    if ( ref($nmap_host_ref->{'os'}->{'osclass'}) eq 'HASH' )
		{
			$host_ref->{'osclass'} = $nmap_host_ref->{'os'}->{'osclass'}->{'osfamily'};
		}
	elsif ( ref($nmap_host_ref->{'os'}->{'osclass'}) eq 'ARRAY')
		{
			$host_ref->{'osclass'} = $nmap_host_ref->{'os'}->{'osclass'}->[0]->{'osfamily'};
		}
	else
		{
			print "DEBUG1: ".ref($nmap_host_ref->{'os'}->{'osclass'})."\n";
		}
	
    if (!$host_ref->{'host_name'}) {
      $host_ref->{'host_name'} = $host_ref->{'address'};
      $host_ref->{'host_name'} =~ s/\./-/g;
      $host_ref->{'host_name'} .= '.' . $config_ref->{'domain'};
    }

    print '  Processed Host (', $host_ref->{'host_name'}, ")\n" if ($options_ref->{'Verbose'});

    # versuch hostnamen zu matchen

    # defaults laden
    foreach my $field (keys % { $config_ref->{'default_host'} }) {
      $host_ref->{$field} = $config_ref->{'default_host'}->{$field} if (!$host_ref->{$field});
    }

    # host_alias default
    $host_ref->{'host_alias'} = $host_ref->{'host_name'} if (!$host_ref->{'host_alias'});

    #pingen gibts im nmap nicht, deswegen packen wirs rein
    my $port_ref = {
      portid => 0,
      service => {
        name => 'ping'
      }
    };
    push @ { $nmap_host_ref->{'ports'}->{'port'}}, $port_ref;
     
    # kennen wir den service?
    foreach my $port_ref (@ { $nmap_host_ref->{'ports'}->{'port'} }) {
      my $matched_service_ref;
      if (defined $port_ref->{'service'}->{'name'} &&
          defined $config_ref->{'service'}->{$port_ref->{'service'}->{'name'}}) {
        $matched_service_ref = $config_ref->{'service'}->{$port_ref->{'service'}->{'name'}};
      }
      elsif (defined $options_ref->{'ignore_unknown_services'}) {
        # nein? verwerfen
        next;
      }
      else {
        # default
        $matched_service_ref = $config_ref->{'service'}->{'default'};
      }

      # dump wenn service disabled
      next if (defined $matched_service_ref->{'disabled'});

      # defaults laden
      my $service_ref = {};
      foreach my $field (keys %{$matched_service_ref}) {
	$service_ref->{$field} = $matched_service_ref->{$field} if (!$service_ref->{$field});
      }

      # allgemeine infos fuer default checks
      if ($matched_service_ref->{'name'} eq 'default') {
	# wenn nmap den service nicht kennt nennen wir ihn unknown
        $port_ref->{'service'}->{'name'} = 'unknown' if (!$port_ref->{'service'}->{'name'});

	$service_ref->{'service_description'} = join('-', $port_ref->{'service'}->{'name'},
                                                          $port_ref->{'protocol'},
                                                          $port_ref->{'portid'});

	$service_ref->{'check_command'} = 'check_' . $port_ref->{'protocol'} . '!' . $port_ref->{'portid'};
      }

      $service_ref->{'port'} = $port_ref;

      $host_ref->{'service'}->{$service_ref->{'port'}->{'portid'}} = $service_ref;
    }

    # hostgroup anhand des BS nenennen.
    foreach my $hostgroup_ref (@{$config_ref->{'hostgroup'}}) {
      next if ($hostgroup_ref->{'group_name'} eq 'default');
      next if (!defined $host_ref->{$hostgroup_ref->{'match'}->{'field'}});
      if ($host_ref->{$hostgroup_ref->{'match'}->{'field'}} =~ /$hostgroup_ref->{'match'}->{'data'}/i) {
	if ($options_ref->{'Verbose'}) {
	  print '    Matched Hostgroup (', $hostgroup_ref->{'group_name'}, ")\n";
	}

	if ($options_ref->{'VERBOSE'}) {
	  print '      Field: (', $hostgroup_ref->{'match'}->{'field'}, ")\n",
		'      Data: (', $host_ref->{$hostgroup_ref->{'match'}->{'field'}}, ")\n",
		'      Match: (', $hostgroup_ref->{'match'}->{'data'}, ")\n" if ($options_ref->{'VERBOSE'});
	}

	$host_ref->{'hostgroup'} = $hostgroup_ref;
	$hostgroup_ref->{'host'}->{$host_ref->{'host_name'}} = $host_ref;
	last;
      }
    }
	# gibt es ein host_template?
	    foreach my $hostgroup_ref (@{$config_ref->{'hostgroup'}}) {
      next if ($hostgroup_ref->{'group_name'} eq 'default');
      next if (!defined $host_ref->{$hostgroup_ref->{'match'}->{'field'}});
      if ($host_ref->{$hostgroup_ref->{'match'}->{'field'}} =~ /$hostgroup_ref->{'match'}->{'data'}/i) {
	if ($options_ref->{'Verbose'}) {
	  print '    Matched Hostgroup (', $hostgroup_ref->{'group_name'}, ")\n";
	}

	if ($options_ref->{'VERBOSE'}) {
	  print '      Field: (', $hostgroup_ref->{'match'}->{'field'}, ")\n",
		'      Data: (', $host_ref->{$hostgroup_ref->{'match'}->{'field'}}, ")\n",
		'      Match: (', $hostgroup_ref->{'match'}->{'data'}, ")\n" if ($options_ref->{'VERBOSE'});
	}

	$host_ref->{'hostgroup'} = $hostgroup_ref;
	$hostgroup_ref->{'host'}->{$host_ref->{'host_name'}} = $host_ref;
	last;
      }
    }

    if (!$host_ref->{'hostgroup'}) {
      print "    Unable to match HostGroup. Setting to (default)\n" if ($options_ref->{'Verbose'});

      $host_ref->{'hostgroup'} = $config_ref->{'default_hostgroup'};
      $config_ref->{'default_hostgroup'}->{'host'}->{$host_ref->{'host_name'}} = $host_ref;
    }

    print "\n" if ($options_ref->{'Verbose'});

    $hosts_ref->{'host'}->{$host_ref->{'address'}} = $host_ref;
  }

  if ($options_ref->{'VERBOSE'}) {
    print "The following HostGroups have been generated\n\n";
    foreach my $hostgroup_ref (@ { $config_ref->{'hostgroup'} }) {
      # gibt es hosts in der hostgroup?
      if (scalar keys % {$hostgroup_ref->{'host'}} ) {
	print '  Hostgroup: ', $hostgroup_ref->{'group_name'}, "\n",
	      '    Alias:         ', $hostgroup_ref->{'group_alias'}, "\n",
	      '    ContactGroups: ', $hostgroup_ref->{'contactgroups'}, "\n",
	      '    Members:       ', join(",\n                   ", sort keys % { $hostgroup_ref->{'host'} }), "\n",
	      "\n";

        # data-dumper output wenn option == verbose
        foreach my $host_ref (sort { $a->{'host_name'} cmp
                             $b->{'host_name'} } values % { $hostgroup_ref->{'host'} }) {
        print Dumper($host_ref) if ($options_ref->{'VERBOSE'});
        }
      }
    }
  }

  return $hosts_ref;
}

sub output_nagios_config {
  my $options_ref = shift;
  my $config_ref = shift;
  my $hosts_ref = shift;
  my $service_template_ref = shift;

  print "\tGenerating '", $options_ref->{'output_file'}, "'\n"; #if ($options_ref->{'Verbose'});

  my $cfg_fh = new IO::File('>' . $options_ref->{'output_file'});
  if ($cfg_fh) {
    my $header = process($config_ref->{'template'}->{'header'}, $hosts_ref->{'run_info'});
    print $cfg_fh $header;

    foreach my $hostgroup_ref (@ { $config_ref->{'hostgroup'} }) {
      # gibt es hosts in der hostgroup?
      if (scalar keys % {$hostgroup_ref->{'host'}} ) {
        # host feld generieren
	$hostgroup_ref->{'hosts'} = join(',', sort keys % { $hostgroup_ref->{'host'} });

        my $hostgroup_header = process($config_ref->{'template'}->{'hostgroup_header'}, $hostgroup_ref);
        print $cfg_fh $hostgroup_header;

        my $hostgroup_entry = process($config_ref->{'template'}->{'hostgroup_entry'}, $hostgroup_ref);
        print $cfg_fh $hostgroup_entry;

	foreach my $host_ref (sort { $a->{'host_name'} cmp
				     $b->{'host_name'} } values % { $hostgroup_ref->{'host'} }) {
	  my $host_header = process($config_ref->{'template'}->{'host_header'}, $host_ref);
	  print $cfg_fh $host_header;

		#template nur einmal schreiben
		my $host_template_entry = process($config_ref->{'template'}->{'host_template_entry'}, $host_ref);
		if($loopcount_host == 0)
		{
		print $cfg_fh $host_template_entry;
		$loopcount_host = 1;
		}
		else
		{
		#tue nichts
		}
	
	  my $host_entry = process($config_ref->{'template'}->{'host_entry'}, $host_ref);
	  print $cfg_fh $host_entry;

	  foreach my $service_ref (sort { $a->{'port'}->{'portid'} <=>
				          $b->{'port'}->{'portid'} } values % { $host_ref->{'service'} }) {
            $service_ref->{'host_name'} = $host_ref->{'host_name'};
		#template nur einmal schreiben
		my $service_template_entry = process($config_ref->{'template'}->{'service_template_entry'}, $service_ref);
		if($loopcount_service == 0)
		{
		print $cfg_fh $service_template_entry;
		$loopcount_service = 1;
		}
		else
		{
		}
	    my $service_header = process($config_ref->{'template'}->{'service_header'}, $service_ref);
	    print $cfg_fh $service_header;
	    my $service_entry = process($config_ref->{'template'}->{'service_entry'}, $service_ref);
	    print $cfg_fh $service_entry;
	    my $service_footer = process($config_ref->{'template'}->{'service_footer'}, $service_ref);
	    print $cfg_fh $service_footer;
	  }
	  my $host_footer = process($config_ref->{'template'}->{'host_footer'}, $host_ref);
	  print $cfg_fh $host_footer;
	}

        my $hostgroup_footer = process($config_ref->{'template'}->{'hostgroup_footer'}, $hostgroup_ref);
        print $cfg_fh $hostgroup_footer;
      }
    }
  }
  else {
    print "Unable to create '", $options_ref->{'output_file'}, "'\n";
  }
}

sub load_configuration {
  my $options_ref = shift;

  my $config_ref = undef;
  if (!-e $options_ref->{'configuration_file'}) {
    print STDERR 'Unable to read (', $options_ref->{'configuration_file'}, ")\n";
  }
  else {
    my @options;
    push @options, $options_ref->{'configuration_file'};
    push @options, 'keyattr' => { service => "+name", address =>'addr'};
    push @options, 'forcearray' => ['host', 'hostgroup', 'service', 'address'];
    push @options, 'suppressempty' => '';

    eval { $config_ref = XML::Simple::XMLin(@options); };
    if ($@) {
      print STDERR 'The follow error occurred while processing (', $options_ref->{'configuration_file'}, ")\n", $@, "\n";
    }
    else {
      # Finde die standard-hostgroups und hosts
      foreach my $type ('host', 'hostgroup') {
        # finde die standard-einträge
	foreach my $conf_ref (@ { $config_ref->{$type} }) {
          my $key = $type . '_name';
          $key = 'group_name' if ($type eq 'hostgroup');

	  if ($conf_ref->{$key} eq 'default') {
	    $config_ref->{'default_' . $type} = $conf_ref;
	    last;
	  }
	}

	if (defined $config_ref->{'default_' . $type}) {
	  # fehlende felder mit default auffüllen
	  foreach my $conf_ref (@ { $config_ref->{$type} }) {
	    my $key = $type . '_name';
	    $key = 'group_name' if ($type eq 'hostgroup');

	    next if ($conf_ref->{$key} eq 'default');

	    foreach my $field (keys % { $config_ref->{'default_' . $type} }) {
	      $conf_ref->{$field} = $config_ref->{'default_' . $type}->{$field} if (!$conf_ref->{$field});
	    }
	  }
        }
        else {
	  print 'WARNING: Unable to locate (default) ', $type, ' configuration in (', $options_ref->{'configuration_file'}, ")!\n";
	}
      }

      if (defined $config_ref->{'service'}->{'default'}) {
        # fehlende felder mit default auffüllen
        foreach my $service_ref (values % { $config_ref->{'service'} }) {
          next if ($service_ref->{'name'} eq 'default');

          foreach my $field (keys % { $config_ref->{'service'}->{'default'} }) {
            $service_ref->{$field} = $config_ref->{'service'}->{'default'}->{$field} if (!$service_ref->{$field});
          }
        }
      }
      else {
        print 'Warning: Unable to locate (default) service configuration in (', $options_ref->{'configuration_file'}, ")!\n";
      }
    }
  }

  return $config_ref;
}

sub load_results {
  my $options_ref = shift;

  my $results_ref = undef;
  if (!-e $options_ref->{'results_file'}) {
    print STDERR 'Unable to read (', $options_ref->{'results_file'}, ")\n";
  }
  else {
    my @options;
    push @options, $options_ref->{'results_file'};
    push @options, 'forcearray' => ['port', 'addr'];

    eval { $results_ref = XML::Simple::XMLin(@options); };
    if ($@) {
      print STDERR 'The follow error occurred while processing (', $options_ref->{'results_file'}, ")\n", $@, "\n";
    }
  }

  return $results_ref;
}

# Adaption von Tools::Template
sub process {
  my $template = shift;
  my $data_ref = shift;
  my $skip_file_tags = shift;

  my ($file, $var, $var_tag, $if, $not, $if_not, $if_not_tag);

  if (!$skip_file_tags) {
    while ($template =~ /{--file_/) {
      ($if_not_tag) = $template =~ /{--file_(.*(?=_file--}))_file--}/;
      $var_tag = "file_$if_not_tag" . "_file";

      $if = $if_not = Tools::Template::process($if_not_tag, $data_ref);
      $not = '';
      if ($if_not =~ /!/) {
        ($if, $not) = split(/!/, $if_not);
      }

      #check for the if
      if (-e "$if") {
        $file = Tools::Template::read($if);
      }
      #otherwise get the not
      elsif (-e "$not") {
        $file = Tools::Template::read($not);
      }
      else {
        $file = '';
      }

      $template =~ s|{--$var_tag--}|$file|g;
    }
  }

  if ($template =~ /{--/) {
    foreach $var ( keys % { $data_ref } ) {
      $template =~ s/{--$var--}/$data_ref->{$var}/g if (defined $data_ref->{$var});
    }
  }

  return $template;
}

# Adaption von Stats::Common
sub seconds2minutes {
  my $time_in_seconds = shift;

  my $minutes = int($time_in_seconds / 60);
  my $seconds = $time_in_seconds - ($minutes * 60);
  my $hours = int($minutes / 60);
  $minutes -= $hours * 60;

  return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}

# Adaption von Tools::TimeDate
sub split_timestamp {
  my $time_ref = {};

  $time_ref->{'timestamp'} = shift;
  $time_ref->{'timestamp'} = time if (!$time_ref->{'timestamp'});

  ($time_ref->{'sec'},
   $time_ref->{'min'},
   $time_ref->{'hour'},
   $time_ref->{'mday'},
   $time_ref->{'mon'},
   $time_ref->{'year'},
   $time_ref->{'wday'},
   $time_ref->{'yday'},
   $time_ref->{'isdst'}) = localtime($time_ref->{'timestamp'});

  $time_ref->{'mon'} = sprintf('%02d', $time_ref->{'mon'} + 1);
  $time_ref->{'mday'} = sprintf('%02d', $time_ref->{'mday'});

  $time_ref->{'hour'} = sprintf('%02d', $time_ref->{'hour'});
  $time_ref->{'min'} = sprintf('%02d', $time_ref->{'min'});
  $time_ref->{'sec'} = sprintf('%02d', $time_ref->{'sec'});

  $time_ref->{'year'} += 1900;

  $time_ref->{'date'} = $time_ref->{'year'} . $time_ref->{'mon'} . $time_ref->{'mday'};
  $time_ref->{'time'} = join(':', $time_ref->{'hour'}, $time_ref->{'min'}, $time_ref->{'sec'});

  $time_ref->{'date_time'} = join('/', $time_ref->{'mon'}, 
                                     $time_ref->{'mday'},
                                     $time_ref->{'year'}) . ' ' . $time_ref->{'time'};

  return $time_ref;
}
1;
__END__

=head1 NAME

nmap2nagios-ng.pl - Perl program to process nmap XML output into Nagios host/hostgroup/services entries

=head1 SYNOPSIS

  Note: I'm not going to go into the theory of using nmap.  Please read the nmap docs for that.

  ./nmap -sS -O -oX nmap.xml myserver.mydomain.com 

  ./nmap2nagios-ng.pl -i -z || -d -r nmap.xml -o new.cfg

  That's it.

  What this program attempts to do is make you life easier by building your hostgroup,
  host and service entries for you.

  It does this by parsing the nmap XML output.

  Here's a sample nmap command:

    nmap -sS -O -oX 192.168.100.1.xml 192.168.100.1

  Which generates this to STDOUT:

  Starting Nmap 4.20 ( http://insecure.org ) at 2008-01-17 12:00 CET
  Interesting ports on victim.nagios.local (192.168.100.1):
  Not shown: 1693 closed ports
  PORT     STATE SERVICE
  135/tcp  open  msrpc
  139/tcp  open  netbios-ssn
  445/tcp  open  microsoft-ds
  3389/tcp open  ms-term-serv
  MAC Address: 00:30:05:53:47:3E (Fujitsu Siemens Computers)
  No exact OS matches for host (If you know what OS is running on it, 
  see http://insecure.org/nmap/submit/ ).
  TCP/IP fingerprint:
  OS:SCAN(V=4.20%D=1/18%OT=135%CT=1%CU=32804%PV=Y%D=1%G=Y%M=003005%TM=47909B
  OS:38%P=i686-pc-linux-gnu)SEQ(SP=106%GCD=1%ISR=10%TI=I%II=I%SS=S%TS=0)OPS(
  OS:O1=M5B4NW0NNT00NNS%O2=M5B4NW0NNT00NNS%O3=M5B4W0NNT00%O4=M5B4NW0NNT00NNS
  OS:%O5=M5B4NW0NNT00NNS%O6=M5B4NNT00NNS)WIN(W1=FFF%W2=FFFF%W3=FFFF%W4=FFFF%
  OS:W5=FFFF%W6=FFFF)ECN(R=Y%DF=Y%T=80%W=FFFF%O=M5BNW0NNS%CC=N%Q=)T1(R=Y%DF=
  OS:Y%T=80%S=O%A=S+%F=AS%RD=0%Q=)T2(R=Y%DF=N%T=80%=0%S=Z%A=S%F=AR%O=%RD=0%Q
  OS:=)T3(R=Y%DF=Y%T=80%W=FFFF%S=O%A=S+%F=AS%O=M5B4N0NNT00NNS%RD=0%Q=)T4(R=Y
  OS:%DF=N%T=80%W=0%S=A%A=O%F=R%O=%RD=0%Q=)T5(R=Y%DFN%T=80%W=0%S=Z%A=S+%F=AR
  OS:%O=%RD=0%Q=)T6(R=Y%DF=N%T=80%W=0%S=A%A=O%F=R%O=RD=0%Q=)T7(R=Y%DF=N%T=80
  OS:%W=0%S=Z%A=S+%F=AR%O=%RD=0%Q=)U1(R=Y%DF=N%T=80%OS=0%IPL=B0%UN=0%RIPL=G%
  OS:RID=G%RIPCK=G%RUCK=G%RUL=G%RUD=G)IE(R=Y%DFI=S%T=I=Z%CD=Z%SI=S%DLI=OS:S)

	Network Distance: 1 hop

	OS detection performed. Please report any incorrect results at 
	http://insecure.org/nmap/submit/ .
	Nmap finished: 1 IP address (1 host up) scanned in 10.000 seconds



=head1 TODO

Caching of previous runs which would allow for merging new and previous scans
into a new output file.

Parseing of host/hostgroup/service entries from existing hosts.cfg for merging
with new/previous scans.

=head1 AUTHORS

Todd A. Green <slaribartfast@awardsforfjords.com>

Michel Sigloch <mail@michel-sigloch.de>

=head1 COPYRIGHT

Copyright (c) 2000-2002 Todd A. Green.
Copyright (c) 2008 Michel Sigloch

All rights reserved.  This program is free software; you can redistribute it
and/or modify it under the same terms as Perl itself.  If you do modify it
though please let the author know cause he likes to hear that someone found 
his work useful. :)

Nagios is a registered trademark of Ethan Galstad.

=head1 DISCLAIMER

It you do something stupid with this software, like wipe out your entire 500 host, 1500 service Nagios configuration,
it's your own fault.  Backups, Backups, Backups, Backups.
Be that as it may, I have beaten the crap out of the code, but I'm sure there is something goofy it 
will do so use it at your own risk.  Please send any bug reports or suggestions to the author.

=head1 SEE ALSO

Nagios @ http://www.nagios.org

nmap @ http://www.insecure.org/nmap/

Nagios-Portal.de @ http://www.nagios-portal.de

Michel's Website @ http://www.michel-sigloch.de

=head1 NAGIOS

Nagios is a registered trademark of Ethan Galstad.

=cut
