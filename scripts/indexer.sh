#!/bin/bash

pwd="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

action=`mysql -uroot -p123 popcorn -e "select if (count(*)>0,'update','freeze') from pn_news where editDate > (select max_doc_datetime from sph_counter where counter_id = 'news')" | tail -1`;

if [[ $action == "update" ]];
then
	echo && echo [ `date "+%Y-%m-%d %H:%M:%S"` ] && echo
	sudo indexer --rotate --config $pwd/../config/sphinxsearch.conf newsDelta
	#sudo indexer --merge news newsDelta --rotate --config $pwd/../config/sphinxsearch.conf
fi