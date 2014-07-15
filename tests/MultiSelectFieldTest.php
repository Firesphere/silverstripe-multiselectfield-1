<?php

class MultiSelectFieldTest extends SapphireTest {

	protected static $fixture_file = 'MultiSelectFieldTest.yml';

	protected $extraDataObjects = array(
		'MultiSelectFieldTest_Department',
		'MultiSelectFieldTest_StaffMember',
	);

	/**
	 * Test that items are saved to the ManyManyList
	 * @return void
	 */
	public function testListSaving() {
		$department = $this->objFromFixture('MultiSelectFieldTest_Department', 'department1');

		$staff1 = MultiSelectFieldTest_StaffMember::create(array('Name' => 'Dixie Normous'));
		$staff1->write();
		$staff2 = $this->objFromFixture('MultiSelectFieldTest_StaffMember', 'staffmember2');

		$field = MultiSelectField::create('StaffMembers', '', $department);
		$field->setValue(array($staff1->ID, $staff2->ID));
		$field->saveInto($department);
		$department->write();

		$staffMembers = $department->StaffMembers()->map('ID', 'Name')->toArray();
		$this->assertArrayHasKey($staff1->ID, $staffMembers);
		$this->assertArrayHasKey($staff2->ID, $staffMembers);
		$this->assertEquals('Dixie Normous', $staffMembers[$staff1->ID]);
		$this->assertEquals('Phil McCreviss', $staffMembers[$staff2->ID]);
	}

	/**
	 * Test that items are saved in the correct order
	 * @return void
	 */
	public function testSortedListSaving() {
		$department = $this->objFromFixture('MultiSelectFieldTest_Department', 'department2');

		$staff1 = $this->objFromFixture('MultiSelectFieldTest_StaffMember', 'staffmember1');
		$staff2 = $this->objFromFixture('MultiSelectFieldTest_StaffMember', 'staffmember2');
		$staff3 = MultiSelectFieldTest_StaffMember::create(array('Name' => 'Dixie Normous'));
		$staff3->write();

		$field = MultiSelectField::create('StaffMembers', '', $department, 'Sort');
		$field->setValue(array($staff3->ID, $staff2->ID, $staff1->ID));
		$field->saveInto($department);
		$department->write();

		$staffMembers = $department->StaffMembers()->sort('Sort')->toArray();
		$this->assertEquals($staff3->ID, $staffMembers[0]->ID);
		$this->assertEquals($staff2->ID, $staffMembers[1]->ID);
		$this->assertEquals($staff1->ID, $staffMembers[2]->ID);

		// Double-check we don't have any false positives
		$field->setValue(array($staff2->ID, $staff1->ID, $staff3->ID));
		$field->saveInto($department);
		$department->write();

		$staffMembers = $department->StaffMembers()->sort('Sort')->toArray();
		$this->assertEquals($staff2->ID, $staffMembers[0]->ID);
		$this->assertEquals($staff1->ID, $staffMembers[1]->ID);
		$this->assertEquals($staff3->ID, $staffMembers[2]->ID);
	}

}

class MultiSelectFieldTest_Department extends DataObject implements TestOnly {

	private static $db = array(
		'Name' => 'Varchar'
	);

	private static $many_many = array(
		'StaffMembers' => 'MultiSelectFieldTest_StaffMember'
	);

	private static $many_many_extraFields = array(
		'StaffMembers' => array(
			'Sort' => 'Int'
		)
	);

}

class MultiSelectFieldTest_StaffMember extends DataObject implements TestOnly {

	private static $db = array(
		'Name' => 'Varchar'
	);

	private static $many_many = array(
		'Departments' => 'MultiSelectFieldTest_Department'
	);

}